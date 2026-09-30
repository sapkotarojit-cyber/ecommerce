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
     * There is no permanent account lockout.
     *
     * Authentication endpoints can therefore use:
     *
     * - per-IP limits
     * - per-account limits
     * - exponential backoff
     *
     * without permanently locking an account.
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
         * Unknown limiter configuration should not
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
     * Email/account is tracked whenever a valid email
     * is available.
     *
     * Raw email addresses are never stored in rate-limit keys.
     *
     * @return array<int, array{kind:string,value:string}>
     */
    private function buildIdentifiers(
        Request $request,
        string $type
    ): array {
        $identifiers = [];

        /*
         * ---------------------------------------------------------
         * IP identifier
         * ---------------------------------------------------------
         */
        $ip = $request->ip();

        if (is_string($ip) && $ip !== '') {
            $identifiers[] = [
                'kind' => 'ip',
                'value' => hash(
                    'sha256',
                    $ip
                ),
            ];
        }

        /*
         * ---------------------------------------------------------
         * Authenticated account identifier
         * ---------------------------------------------------------
         *
         * Sensitive authenticated endpoints usually do not submit an
         * email address. Bind their account bucket to the authenticated
         * principal so the account limit is actually enforced.
         */
        foreach (['web', 'dokan', 'admin'] as $guard) {
            $guardUser = auth()->guard($guard)->user();

            if ($guardUser && $guardUser->getAuthIdentifier() !== null) {
                $identifiers[] = [
                    'kind' => 'account',
                    'value' => hash(
                        'sha256',
                        $guard . ':user:' . (string) $guardUser->getAuthIdentifier()
                    ),
                ];

                break;
            }
        }

        /*
         * ---------------------------------------------------------
         * Email/account identifier
         * ---------------------------------------------------------
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
                    $email
                ),
            ];
        }

        /*
         * ---------------------------------------------------------
         * Pending verification account
         * ---------------------------------------------------------
         *
         * Verification can use the pending user ID when the
         * request does not contain an email field.
         */
        if (
            $type === 'verification' &&
            $request->hasSession() &&
            $request->session()->has('pending_user_id')
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
                        'user:' . (string) $pendingUserId
                    ),
                ];
            }
        }

        /*
         * ---------------------------------------------------------
         * Remove duplicate identifiers
         * ---------------------------------------------------------
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
     * Enforce a single IP/account limit.
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
         * Some limiter types may intentionally have
         * only an IP configuration.
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
         * ---------------------------------------------------------
         * Active exponential cooldown
         * ---------------------------------------------------------
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
         * ---------------------------------------------------------
         * Current request window
         * ---------------------------------------------------------
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
     * Clear the exhausted request window.
     *
     * Otherwise, after the cooldown expires,
     * the same attempts count immediately triggers
     * another cooldown.
     */
    RateLimiter::clear($attemptKey);

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
         * Count the current request.
         */
        RateLimiter::hit(
            $attemptKey,
            $decaySeconds
        );
    }

    /**
     * Calculate exponential backoff.
     *
     * Example:
     *
     * base = 5
     *
     * violation 1 = 5 seconds
     * violation 2 = 10 seconds
     * violation 3 = 20 seconds
     * violation 4 = 40 seconds
     *
     * until the configured maximum is reached.
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

        /*
         * Number of previous violations within the
         * configured violation window.
         */
        $violations =
            RateLimiter::attempts(
                $violationKey
            );

        /*
         * Prevent integer overflow from malicious or
         * corrupted limiter state.
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
     * Return a consistent HTTP 429 response.
     *
     * This method always aborts the request and therefore never returns.
     */
    private function reject(
        Request $request,
        int $retryAfter
    ): never {
        $retryAfter = max(1, $retryAfter);

        if ($request->expectsJson() || $request->ajax()) {
            abort(
                response()->json([
                    'message' => 'Too many requests. Please try again later.',
                    'retry_after' => $retryAfter,
                ], 429)->header(
                    'Retry-After',
                    (string) $retryAfter
                )
            );
        }

        abort(
            response()
                ->view('errors.429', [
                    'retryAfter' => $retryAfter,
                ], 429)
                ->header(
                    'Retry-After',
                    (string) $retryAfter
                )
        );
    }
}

