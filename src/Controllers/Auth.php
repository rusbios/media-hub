<?php

namespace RusBios\MediaHub\Controllers;

use App\Http\Controllers\Controller;
use RusBios\MediaHub\Models\User;
use RusBios\MediaHub\Utils\{Crypt, MbString};
use RusBios\MediaHub\Validations\{UserLogIn, UserNewPassword, UserReg};
use Illuminate\Database\Eloquent\Model;
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

            return $this->getSuccess(['redirect' => route('home')]);
        }

        return $this->getError('incorrectly filled data');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function restorePass(Request $request): Response
    {
        $user = User::query()
            ->where('email', $request->get('email', ''))
            ->first();

        //TODO send email

        return $this->getSuccess(['redirect' => route('home')]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function confirmEmail(Request $request): Response
    {
        $user = $this->getUser($request->get('email', ''));

        if ($user) {
            $user->email_verified_at = new \DateTime();
            $user->save();
        }

        return $this->getSuccess([
            'success' => !empty($user),
            'email' => $user ? $user->email : null,
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function newPass(Request $request): Response
    {
        $data = UserNewPassword::getValidData($request);

        if ($data) {
            $user = User::query()
                ->where('remember_token', $data['token'])
                ->first();

            if ($user) {
                $user->pasword = Hash::make($data['password']);
                $user->remember_token = MbString::generateRandomNumber(100);
                $user->save();
                FAuth::login($user, true);
            }

            return $this->getSuccess(['user' => $user->all()]);
        }

        return $this->getError('Incorrect token');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function logIn(Request $request): Response
    {
        $data = UserLogIn::getValidData($request);

        if ($data) {
            /** @var User $user */
            $user = User::query()
                ->where('email', $data['email'])
                ->first();

            if ($user && Hash::check($data['password'], $user->password)) {
                FAuth::login($user, true);
                return $this->getSuccess(['redirect' => route('home')]);
            }
        }

        return $this->getError('Incorrect login or password');
    }

    /**
     * @return Response
     */
    public function logOut(): Response
    {
        FAuth::logout();

        return $this->getSuccess(['redirect' => route('home')]);
    }

    /**
     * @return Response
     */
    public function info(): Response
    {
        return $this->getSuccess([
            'auth' => FAuth::check(),
            'user' => FAuth::check() ? User::find(FAuth::id()) : null,
        ]);
    }

    /**
     * @param string $crypt
     * @return Model|User|null
     */
    private function getUser(string $crypt): ?User
    {
        return User::query()
            ->where('email', Crypt::decryptString($crypt))
            ->first();
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
                $token = Crypt::encryptArray([
                    'user_id' => $user->id
                ]);
                return $this->getSuccess(['token' => $token]);
            }
        }

        return $this->getError('invalid data');
    }

    public static function route(): void
    {
        Route::get('user/info', 'Auth@info')->name('user_info');
        Route::post('user/reg', 'Auth@registration')->name('user_reg');
        Route::get('user/logout', 'Auth@logOut')->name('logout');
        Route::get('user/login', 'Auth@logIn')->name('login');
        Route::get('user/restore', 'Auth@restorePass')->name('restore_pass');
        Route::get('user/confirm', 'Auth@confirmEmail')->name('confirm_email');
        Route::get('user/password', 'Auth@newPass')->name('new_pass');
        Route::post('user/api_token', 'Auth@getApiToken')->name('get_api_token');
    }
}
