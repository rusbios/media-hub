<?php

namespace MediaHub\Services;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use MediaHub\Models\UserModels;
use MediaHub\Utils\MbString;
use MediaHub\Validations\{UserLogInValidation, UserNewPasswordValidation, UserRegValidation};

class AuthService
{
    use ResponseTrait;

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function registration(Request $request): array
    {
        $data = UserRegValidation::getValidData($request);

        $data['password'] = Hash::make($data['password']);
        if ($data) {
            /** @var UserModels $user */
            $user = (new UserModels())->fill($data);

            if (UserRegValidation::isDuplicate($user)) {
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
        /** @var UserModels $user */
        $user = UserModels::query()
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
        /** @var UserModels $user */
        $user = UserModels::query()
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
            $user = UserModels::query()
                ->where('remember_token', $request->get('token'))
                ->first();
        } else {
            $user = $this->decodeToken($request);
        }

        if (!$user) {
            throw new Exception('Invalid token');
        }

        $data = UserNewPasswordValidation::getValidData($request);

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
        $data = UserLogInValidation::getValidData($request);

        if ($data) {
            /** @var UserModels $user */
            $user = UserModels::query()
                ->where('email', $data['email'])
                ->first();

            if ($user && Hash::check($data['password'], $user->password)) {
                return ['token' => TokenService::created($request, $user)];
            }
        }

        throw new Exception('invalid data');
    }
}
