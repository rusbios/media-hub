<?php

namespace MediaHub\Services;

use Illuminate\Http\Request;
use MediaHub\Models\User;
use MediaHub\Utils\Crypt;

class Token
{
    protected const LIFETIME_HOURS = 24;

    /**
     * @param Request $request
     * @param User $user
     * @param int|null $lifetimeHours
     * @return string
     */
    public static function created(Request $request, User $user, ?int $lifetimeHours): string
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

        if (
            empty($data['user_id'])
            || empty($data['ip'])
            || empty($data['decay_ts'])
            || empty($data['email'])
        ) {
            return false;
        }

        if ($request->ip() == $data['ip'] && (new \DateTime())->getTimestamp() > $data['decay_ts']) {
            return true;
        }

        return false;
    }

    /**
     * @param string $token
     * @return User|null
     */
    public static function getUser(string $token): ?User
    {
        $data = Crypt::decryptArray($token);

        if (empty($data['user_id'])) {
            return null;
        }

        return User::find($data['user_id']);
    }
}
