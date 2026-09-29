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

    /**
     * Validate a request while rejecting unexpected top-level fields.
     *
     * Laravel's normal validate() method validates known fields but ignores
     * extra input. Security-sensitive endpoints should instead reject any
     * field that is not explicitly part of their schema.
     */
    protected function validateStrict(Request $request, array $rules, array $allowedExtra = []): array
    {
        $allowed = [
            '_token',
            '_method',
            ...$allowedExtra,
        ];

        foreach (array_keys($rules) as $field) {
            $allowed[] = explode('.', $field, 2)[0];
        }

        $allowed = array_values(array_unique($allowed));
        $unexpected = array_values(array_diff(array_keys($request->all()), $allowed));

        if ($unexpected !== []) {
            throw ValidationException::withMessages([
                'request' => 'The request contains unsupported fields: ' . implode(', ', $unexpected),
            ]);
        }

        return $this->validate($request, $rules);
    }
}
