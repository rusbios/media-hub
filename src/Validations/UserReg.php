<?php

namespace RusBios\MediaHub\Validations;

use Illuminate\Database\Eloquent\Model;
use RusBios\MediaHub\Models\User;
use Illuminate\Http\Request;

class UserReg implements ValidInterface
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
    public static function getValidData(Request $request): ?array
    {
        return static::isValid($request) ? $request->all(['name', 'password', 'email']) : null;
    }

    /**
     * @param User|Model $user
     * @return bool
     */
    public static function isDuplicate(User $user): bool
    {
        return User::query()->where('email', $user->email)->exists();
    }
}
