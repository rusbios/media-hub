<?php

namespace RusBios\MediaHub\Validations;

use Illuminate\Http\Request;

class Storage implements ValidInterface
{
    public static function isValid(Request $request): bool
    {
        $data = $request->all(['host', 'login', 'password']);

        return !empty($data['host'])
            && !empty($data['login'])
            && !empty($data['password']);
    }

    public static function getValidData(Request $request): ?array
    {
        return static::isValid($request) ? $request->all(['host', 'login', 'password', 'port', 'default']) : null;
    }
}
