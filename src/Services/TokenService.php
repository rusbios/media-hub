<?php

namespace MediaHub\Services;

use Illuminate\Http\Request;
use MediaHub\Models\UserModels;
use MediaHub\Utils\Crypt;

class TokenService
{
    protected const LIFETIME_HOURS = 24;

    /**
     * @param Request $request
     * @param UserModels $user
     * @param int|null $lifetimeHours
     * @return string
     */
    public static function created(Request $request, UserModels $user, int $lifetimeHours = null): string
    {
        if (!$lifetimeHours) $lifetimeHours = self::LIFETIME_HOURS;

        return Crypt::encryptArray([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'decay_ts' => (new \DateTime())->modify(sprintf('+%s hours', $lifetimeHours))->getTimestamp(),
        ]);
    }

    /**
     * @param Request $request
     * @param string $token
     * @return bool
     */
    public static function isValid(Request $request, string $token): bool
    {
        $data = Crypt::decryptArray($token);

        if (empty($data['user_id'])
            || empty($data['ip'])
            || empty($data['decay_ts'])
            || empty($data['email'])) {
            return false;
        }

        if ($request->ip() == $data['ip']
            && (new \DateTime())->getTimestamp() < $data['decay_ts']) {
            return true;
        }

        return false;
    }

    /**
     * @param string $token
     * @return UserModels|null
     */
    public static function getUser(string $token): ?UserModels
    {
        $data = Crypt::decryptArray($token);

        if (empty($data['user_id'])) {
            return null;
        }

        $user = UserModels::find($data['user_id']);

        if ($user && $user->email == $data['email']) {
            return $user;
        }

        return null;
    }
}
