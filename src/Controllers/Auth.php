<?php

namespace RusBios\MediaHub\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\AuthenticationException;
use RusBios\MediaHub\Models\User;
use RusBios\MediaHub\Utils\MbString;
use RusBios\MediaHub\Services\Token;
use RusBios\MediaHub\Validations\{UserLogIn, UserNewPassword, UserReg};
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\{Auth as FAuth, Hash, Route};

class Auth extends Controller
{
    use ResponseTrait;

    /**
     * @param Request $request
     * @return Response
     */
    public function registration(Request $request): Response
    {
        $data = UserReg::getValidData($request);

        $data['password'] = Hash::make($data['password']);
        if ($data) {
            $user = (new User())->fill($data);

            if (UserReg::isDuplicate($user)) {
                return $this->getError('Such user already exists');
            }

            $user->save();

            FAuth::login($user, true);

            return $this->getSuccess(['user' => $user->jsonSerialize(true)]);
        }

        return $this->getError('incorrectly filled data');
    }

    /**
     * @param string $email
     * @return Response
     */
    public function recover(string $email): Response
    {
        /** @var User $user */
        $user = User::query()
            ->where('email', $email)
            ->first();

        if (!$user) {
            return $this->getError('invalid email');
        }

        $user->remember_token = MbString::generateSymbols(100);
        $user->save();

        //TODO send email

        return $this->getSuccess([]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function confirmEmail(Request $request): Response
    {
        /** @var User $user */
        $user = User::query()
            ->where('email', $request->get('email', ''))
            ->first();

        if (!$user) {
            return $this->getError('invalid email');
        }

        $user->email_verified_at = new \DateTime();
        $user->save();

        return $this->getSuccess([]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function newPass(Request $request): Response
    {
        if ($request->get('token')) {
            $user = User::query()
                ->where('remember_token', $request->get('token'))
                ->first();
        } else {
            try {
                $user = $this->decodeToken($request);
            } catch (AuthenticationException $e) {
                return $this->getError($e->getMessage());
            }
        }

        if (!$user) {
            return $this->getError('Invalid token');
        }

        $data = UserNewPassword::getValidData($request);

        if (!$data) {
            return $this->getError('Invalid password');
        }

        $user->remember_token = null;
        $user->password = Hash::make($data['password']);

        $user->save();

        return $this->getSuccess([]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function info(Request $request): Response
    {
        try {
            $user = $this->decodeToken($request);
        } catch (AuthenticationException $e) {
            return $this->getError($e->getMessage(), 401);
        }

        return $this->getSuccess(['user' => $user->jsonSerialize(true)]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getApiToken(Request $request): Response
    {
        $data = UserLogIn::getValidData($request);

        if ($data) {
            /** @var User $user */
            $user = User::query()
                ->where('email', $data['email'])
                ->first();

            if ($user && Hash::check($data['password'], $user->password)) {
                return $this->getSuccess(['token' => Token::created($request, $user)]);
            }
        }

        return $this->getError('invalid data');
    }

    public static function route(): void
    {
        Route::prefix('auth')->group(function () {
            Route::get('info', 'Auth@info')->name('auth_info');
            Route::post('api_token', 'Auth@info')->name('auth_api_token');
            Route::post('reg', 'Auth@registration')->name('auth_reg');
            Route::get('recover/{email}', 'Auth@recover')->name('auth_recover');
            Route::post('new_pas/{token}', 'Auth@newPass')->name('auth_new_pass');
            Route::get('confirm', 'Auth@confirmEmail')->name('confirm_email');
        });
    }
}
