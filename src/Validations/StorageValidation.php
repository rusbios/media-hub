<?php

namespace MediaHub\Validations;

use Illuminate\Http\Request;

class StorageValidation implements ValidInterface
{
    public static function isValid(Request $request): bool
    {
        $data = $request->all(['host', 'login', 'password']);

        return !empty($data['host'])
            && !empty($data['login'])
            && !empty($data['password']);
    }

    public static function getValidData(Request $request)
    {
        return static::isValid($request) ? $request->all(['host', 'login', 'password', 'port', 'default']) : null;
    }
}
