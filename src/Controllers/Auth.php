<?php

namespace MediaHub\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use MediaHub\Services\{AuthService, ResponseTrait};
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\Route;

class Auth extends Controller
{
    use ResponseTrait;

    /** @var AuthService */
    private $authService;

    /**
     * Auth constructor.
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function registration(Request $request): Response
    {
        try {
            return $this->getSuccess($this->authService->registration($request));
        } catch (Exception $e) {
            return $this->getError($e->getMessage(), null, $request);
        }
    }

    /**
     * @param string $email
     * @return Response
     */
    public function recover(string $email): Response
    {
        try {
            return $this->getSuccess($this->authService->recover($email));
        } catch (Exception $e) {
            return $this->getError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function confirmEmail(Request $request): Response
    {
        try {
            return $this->getSuccess($this->authService->confirmEmail($request));
        } catch (Exception $e) {
            return $this->getError($e->getMessage(), null, $request);
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function newPass(Request $request): Response
    {
        try {
            return $this->getSuccess($this->authService->newPass($request));
        } catch (AuthenticationException $e) {
            return $this->getError($e->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return $this->getError($e->getMessage(), null, $request);
        }
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
            return $this->getError($e->getMessage(), Response::HTTP_FORBIDDEN);
        }

        return $this->getSuccess(['user' => $user->jsonSerialize(true)]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getApiToken(Request $request): Response
    {
        try {
            return $this->getSuccess($this->authService->getApiToken($request));
        } catch (Exception $e) {
            return $this->getError($e->getMessage(), null, $request);
        }
    }

    public static function route()
    {
        Route::prefix('auth')->namespace('MediaHub\Controllers')->group(function () {
            Route::match(['GET', 'POST'], 'info', 'Auth@info')->name('api-auth-info');
            Route::post('api_token', 'Auth@getApiToken')->name('api-auth-token');
            Route::post('reg', 'Auth@registration')->name('api-auth-reg');
            Route::get('recover/{email}', 'Auth@recover')->name('api-auth-recover');
            Route::post('new_pas/{token}', 'Auth@newPass')->name('api-auth-new-pass');
            Route::get('confirm', 'Auth@confirmEmail')->name('api-confirm-email');
        });
    }
}
