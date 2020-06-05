<?php

namespace RusBios\MediaHub\Validations;

use Illuminate\Http\Request;

class Album implements ValidInterface
{
    public static function isValid(Request $request): bool
    {
        $data = $request->all(['name']);

        return !empty($data['name']);
    }

    public static function getValidData(Request $request): ?array
    {
        return static::isValid($request) ? $request->all(['name', 'access']) : null;
    }
}
