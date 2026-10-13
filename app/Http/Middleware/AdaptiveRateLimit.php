<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AdaptiveRateLimit
{
    /**
     * Apply configurable IP/account rate limits
     * with exponential backoff.
     *
     * No permanent account lockout is used.
     */
    public function handle(
        Request $request,
        Closure $next,
        string $type = 'sensitive'
    ): Response {
        $config = config(
            "security.rate_limits.{$type}"
        );

        /*
         * Unknown limiter configuration should never
         * break the application.
         */
        if (!is_array($config)) {
            return $next($request);
        }

        $identifiers = $this->buildIdentifiers(
            $request,
            $type
        );

        foreach ($identifiers as $identifier) {
            $this->enforceLimit(
                request: $request,
                type: $type,
                identifier: $identifier,
                config: $config
            );
        }

        return $next($request);
    }

    /**
     * Build IP and account identifiers.
     *
     * IP is always tracked.
     *
     * Account is tracked using:
     *
     * - authenticated user
     * - submitted email
     * - pending verification user
     *
     * Raw email addresses are never stored in limiter keys.
     *
     * @return array<int, array{kind:string,value:string}>
     */
    private function buildIdentifiers(
        Request $request,
        string $type
    ): array {
        $identifiers = [];

        /*
        |--------------------------------------------------------------------------
        | IP
        |--------------------------------------------------------------------------
        */

        $ip = $request->ip();

        if (
            is_string($ip) &&
            $ip !== ''
        ) {
            $identifiers[] = [
                'kind' => 'ip',

                'value' => hash(
                    'sha256',
                    $ip
                ),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Authenticated account
        |--------------------------------------------------------------------------
        |
        | Include Sanctum because the API uses auth:sanctum.
        |
        */

        foreach (
            [
                'sanctum',
                'web',
                'dokan',
                'admin',
            ] as $guard
        ) {
            $guardUser = auth()
                ->guard($guard)
                ->user();

            if (
                $guardUser &&
                $guardUser->getAuthIdentifier() !== null
            ) {
                $identifiers[] = [
                    'kind' => 'account',

                    'value' => hash(
                        'sha256',
                        $guard
                        . ':user:'
                        . (string) $guardUser
                            ->getAuthIdentifier()
                    ),
                ];

                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Submitted email
        |--------------------------------------------------------------------------
        */

        $email = strtolower(
            trim(
                (string) $request->input('email')
            )
        );

        if (
            $email !== '' &&
            filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            $identifiers[] = [
                'kind' => 'account',

                'value' => hash(
                    'sha256',
                    'email:' . $email
                ),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Pending verification account
        |--------------------------------------------------------------------------
        */

        if (
            $type === 'verification' &&
            $request->hasSession() &&
            $request->session()->has(
                'pending_user_id'
            )
        ) {
            $pendingUserId =
                $request->session()->get(
                    'pending_user_id'
                );

            if (
                is_int($pendingUserId) ||
                (
                    is_string($pendingUserId) &&
                    ctype_digit($pendingUserId)
                )
            ) {
                $identifiers[] = [
                    'kind' => 'account',

                    'value' => hash(
                        'sha256',
                        'user:'
                        . (string) $pendingUserId
                    ),
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Remove duplicates
        |--------------------------------------------------------------------------
        */

        $unique = [];

        foreach ($identifiers as $identifier) {
            $key =
                $identifier['kind']
                . ':'
                . $identifier['value'];

            $unique[$key] = $identifier;
        }

        return array_values($unique);
    }

    /**
     * Enforce one IP/account limiter.
     *
     * @param array<string,mixed> $config
     * @param array{kind:string,value:string} $identifier
     */
    private function enforceLimit(
        Request $request,
        string $type,
        array $identifier,
        array $config
    ): void {
        $kind = $identifier['kind'];
        $value = $identifier['value'];

        $limit = $config[$kind] ?? null;

        /*
         * This limiter type may intentionally have
         * only one identifier type.
         */
        if (
            !is_array($limit) ||
            count($limit) < 2
        ) {
            return;
        }

        $maxAttempts = max(
            1,
            (int) $limit[0]
        );

        $decaySeconds = max(
            1,
            (int) $limit[1]
        );

        $prefix =
            'adaptive-rate:'
            . $type
            . ':'
            . $kind
            . ':'
            . $value;

        $attemptKey =
            $prefix . ':attempts';

        $violationKey =
            $prefix . ':violations';

        $cooldownKey =
            $prefix . ':cooldown';

        /*
        |--------------------------------------------------------------------------
        | Existing cooldown
        |--------------------------------------------------------------------------
        */

        $cooldownRemaining =
            RateLimiter::availableIn(
                $cooldownKey
            );

        if ($cooldownRemaining > 0) {
            $this->reject(
                $request,
                $cooldownRemaining
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Current request window
        |--------------------------------------------------------------------------
        */

        $attempts =
            RateLimiter::attempts(
                $attemptKey
            );

        if ($attempts >= $maxAttempts) {

            $backoff = $this->createBackoff(
                config: $config,
                violationKey: $violationKey,
                decaySeconds: $decaySeconds
            );

            /*
             * Clear exhausted window so that after
             * cooldown expires the request gets a
             * fresh window.
             */
            RateLimiter::clear(
                $attemptKey
            );

            RateLimiter::hit(
                $cooldownKey,
                $backoff
            );

            $this->reject(
                $request,
                $backoff
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Count current request
        |--------------------------------------------------------------------------
        */

        RateLimiter::hit(
            $attemptKey,
            $decaySeconds
        );
    }

    /**
     * Calculate exponential backoff.
     *
     * base = 5
     *
     * violation 0 = 5 seconds
     * violation 1 = 10 seconds
     * violation 2 = 20 seconds
     * violation 3 = 40 seconds
     *
     * capped by configured maximum.
     *
     * @param array<string,mixed> $config
     */
    private function createBackoff(
        array $config,
        string $violationKey,
        int $decaySeconds
    ): int {
        $backoffConfig =
            $config['backoff'] ?? [];

        if (!is_array($backoffConfig)) {
            $backoffConfig = [];
        }

        $base = max(
            1,
            (int) (
                $backoffConfig['base']
                ?? 1
            )
        );

        $maximum = max(
            $base,
            (int) (
                $backoffConfig['max']
                ?? $decaySeconds
            )
        );

        $violationWindow = max(
            $decaySeconds,
            (int) (
                $backoffConfig['violation_window']
                ?? $maximum
            )
        );

        $violations =
            RateLimiter::attempts(
                $violationKey
            );

        /*
         * Prevent integer overflow.
         */
        $exponent = min(
            30,
            max(
                0,
                (int) $violations
            )
        );

        $backoff =
            $base * (2 ** $exponent);

        $backoff = min(
            $maximum,
            $backoff
        );

        /*
         * Record this violation.
         */
        RateLimiter::hit(
            $violationKey,
            $violationWindow
        );

        return max(
            1,
            (int) $backoff
        );
    }

    /**
     * Return consistent HTTP 429 response.
     */
    private function reject(
        Request $request,
        int $retryAfter
    ): never {
        $retryAfter = max(
            1,
            $retryAfter
        );

        if (
            $request->expectsJson() ||
            $request->ajax()
        ) {
            abort(
                response()->json([
                    'message' =>
                        'Too many requests. Please try again later.',

                    'retry_after' =>
                        $retryAfter,
                ], 429)->header(
                    'Retry-After',
                    (string) $retryAfter
                )
            );
        }

        abort(
            response()
                ->view(
                    'errors.429',
                    [
                        'retryAfter' =>
                            $retryAfter,
                    ],
                    429
                )
                ->header(
                    'Retry-After',
                    (string) $retryAfter
                )
        );
    }
}