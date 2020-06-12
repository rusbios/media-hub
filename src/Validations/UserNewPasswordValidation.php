<?php

namespace MediaHub\Validations;

use Illuminate\Http\Request;

class UserNewPasswordValidation implements ValidInterface
{
    public static function isValid(Request $request): bool
    {
        $data = $request->all(['password', 'confirm_password']);

        return !empty($data['password'])
            && !empty($data['confirm_password'])
            && $data['password'] === $data['confirm_password'];
    }

    public static function getValidData(Request $request)
    {
        return static::isValid($request) ? $request->all(['password']) : null;
    }
}
