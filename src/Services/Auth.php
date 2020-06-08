<?php

namespace RusBios\MediaHub\Services;

use Exception;
use Illuminate\Auth\{AuthenticationException, Request, Response};
use Illuminate\Support\Facades\Hash;
use RusBios\MediaHub\Models\User;
use RusBios\MediaHub\Utils\MbString;
use RusBios\MediaHub\Validations\{UserLogIn, UserNewPassword, UserReg};

class Auth
{
    use ResponseTrait;

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function registration(Request $request): array
    {
        $data = UserReg::getValidData($request);

        $data['password'] = Hash::make($data['password']);
        if ($data) {
            /** @var User $user */
            $user = (new User())->fill($data);

            if (UserReg::isDuplicate($user)) {
                throw new Exception('Such user already exists');
            }

            $user->save();

            return ['user' => $user->jsonSerialize(true)];
        }

        throw new Exception('incorrectly filled data');
    }

    /**
     * @param string $email
     * @return array
     * @throws Exception
     */
    public function recover(string $email): array
    {
        /** @var User $user */
        $user = User::query()
            ->where('email', $email)
            ->first();

        if (!$user) {
            throw new Exception('invalid email');
        }

        $user->remember_token = MbString::generateSymbols(100);
        $user->save();

        //TODO send email

        return [];
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function confirmEmail(Request $request): array
    {
        /** @var User $user */
        $user = User::query()
            ->where('email', $request->get('email', ''))
            ->first();

        if (!$user) {
            throw new Exception('invalid email');
        }

        $user->email_verified_at = new \DateTime();
        $user->save();

        return [];
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthenticationException
     * @throws Exception
     */
    public function newPass(Request $request): array
    {
        if ($request->get('token')) {
            $user = User::query()
                ->where('remember_token', $request->get('token'))
                ->first();
        } else {
            $user = $this->decodeToken($request);
        }

        if (!$user) {
            throw new Exception('Invalid token');
        }

        $data = UserNewPassword::getValidData($request);

        if (!$data) {
            throw new Exception('Invalid password');
        }

        $user->remember_token = null;
        $user->password = Hash::make($data['password']);
        $user->save();

        return [];
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function getApiToken(Request $request): array
    {
        $data = UserLogIn::getValidData($request);

        if ($data) {
            /** @var User $user */
            $user = User::query()
                ->where('email', $data['email'])
                ->first();

            if ($user && Hash::check($data['password'], $user->password)) {
                return ['token' => Token::created($request, $user)];
            }
        }

        throw new Exception('invalid data');
    }
}
