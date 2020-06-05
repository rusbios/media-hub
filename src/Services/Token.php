<?php

namespace RusBios\MediaHub\Services;

use Illuminate\Http\Request;
use RusBios\MediaHub\Models\User;
use RusBios\MediaHub\Utils\Crypt;

class Token
{
    protected const TIME_LIVE_TOKEN = 1; //week

    /**
     * @param Request $request
     * @param User $user
     * @return string
     */
    public static function created(Request $request, User $user): string
    {
        return Crypt::encryptArray([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'login_ts' => (new \DateTime())->getTimestamp(),
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
            || empty($data['login_ts'])
            || empty($data['email'])
        ) {
            return false;
        }

        $timeLive = (new \DateTime())
            ->modify('-' . self::TIME_LIVE_TOKEN . ' week')
            ->getTimestamp();

        if ($request->ip() == $data['ip'] && $timeLive < $data['login_ts']) {
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