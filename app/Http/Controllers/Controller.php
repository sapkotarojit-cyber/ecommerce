<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

abstract class Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function validateStrict(
        Request $request,
        array $rules,
        array $extra = []
    ): array {
        $allowed = ['_token', '_method', ...$extra];

        foreach (array_keys($rules) as $field) {
            $allowed[] = explode('.', $field, 2)[0];
        }

        $unexpected = array_diff(
            array_keys($request->all()),
            array_unique($allowed)
        );

        if ($unexpected) {
            throw ValidationException::withMessages([
                'request' => 'Unsupported field(s): ' . implode(', ', $unexpected),
            ]);
        }

        return $this->validate($request, $rules);
    }
}