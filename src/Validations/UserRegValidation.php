<?php

namespace MediaHub\Validations;

use MediaHub\Models\UserModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class UserRegValidation implements ValidInterface
{
    /**
     * @param Request $request
     * @return bool
     */
    public static function isValid(Request $request): bool
    {
        $data = $request->all(['name', 'password', 'email', 'confirm_password']);

        return !empty($data['email'])
            && !empty($data['password'])
            && !empty($data['confirm_password'])
            && $data['password'] === $data['confirm_password']
            && !empty($data['name']);
    }

    /**
     * @param Request $request
     * @return array|null
     */
    public static function getValidData(Request $request)
    {
        return static::isValid($request) ? $request->all(['name', 'password', 'email']) : null;
    }

    /**
     * @param UserModels|Model $user
     * @return bool
     */
    public static function isDuplicate(UserModels $user): bool
    {
        return UserModels::query()->where('email', $user->email)->exists();
    }
}
