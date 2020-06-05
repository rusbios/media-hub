<?php

namespace RusBios\MediaHub\Validations;

use Illuminate\Http\Request;

class UserNewPassword implements ValidInterface
{

    public static function isValid(Request $request): bool
    {
        $data = $request->all(['token', 'password', 'confirm_password']);

        return !empty($data['token'])
            && !empty($data['password'])
            && !empty($data['confirm_password'])
            && $data['password'] === $data['confirm_password'];
    }

    public static function getValidData(Request $request): ?array
    {
        return static::isValid($request) ? $request->all(['password', 'token']) : null;
    }
}
