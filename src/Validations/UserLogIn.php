<?php

namespace MediaHub\Validations;

use Illuminate\Http\Request;

class UserLogIn implements ValidInterface
{

    public static function isValid(Request $request): bool
    {
        $data = $request->all(['email', 'password']);

        return !empty($data['email']) && !empty($data['password']);
    }

    public static function getValidData(Request $request)
    {
        return static::isValid($request) ? $request->all(['password', 'email']) : null;
    }
}
