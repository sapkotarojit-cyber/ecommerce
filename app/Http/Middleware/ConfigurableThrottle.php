<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ConfigurableThrottle
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route()?->getName();
        $config = config("security.rate_limits.{$route}");

        if (!$config) {
            return $next($request);
        }

        $keys = [];

        if (isset($config['ip'])) {
            $keys[] = [
                'key' => "security:{$route}:ip:{$request->ip()}",
                'limit' => $config['ip'][0],
                'decay' => $config['ip'][1],
            ];
        }

        if (isset($config['account'])) {
            $account = $this->accountKey($request);

            if ($account !== null) {
                $keys[] = [
                    'key' => "security:{$route}:account:{$account}",
                    'limit' => $config['account'][0],
                    'decay' => $config['account'][1],
                ];
            }
        }

        foreach ($keys as $bucket) {
            $attempts = RateLimiter::hit($bucket['key'], $bucket['decay']);

            if ($attempts > $bucket['limit']) {
                $excess = $attempts - $bucket['limit'];
                $base = $config['backoff'][0] ?? 1;
                $maximum = $config['backoff'][1] ?? $bucket['decay'];

                $delay = min(
                    $maximum,
                    $base * (2 ** min($excess - 1, 20))
                );

                return $this->tooManyRequests($delay);
            }
        }

        $response = $next($request);

        foreach ($keys as $bucket) {
            $response->headers->set(
                'X-RateLimit-Limit',
                (string) $bucket['limit']
            );

            $response->headers->set(
                'X-RateLimit-Remaining',
                (string) max(
                    0,
                    $bucket['limit'] - RateLimiter::attempts($bucket['key'])
                )
            );
        }

        return $response;
    }

    private function accountKey(Request $request): ?string
    {
        $email = $request->input('email');

        if (is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return hash('sha256', strtolower(trim($email)));
        }

        $userId = session('pending_user_id');

        if ($userId !== null) {
            return 'user-' . (string) $userId;
        }

        if ($request->user()) {
            return 'user-' . $request->user()->getAuthIdentifier();
        }

        return null;
    }

    private function tooManyRequests(int $seconds): Response
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $seconds,
            ], 429)->header('Retry-After', $seconds);
        }

        return response(
            'Too many requests. Please try again later.',
            429
        )->header('Retry-After', $seconds);
    }
}