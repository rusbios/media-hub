<?php

namespace RusBios\MediaHub\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Auth\AuthenticationException;
use RusBios\MediaHub\Services\{Auth as SAuth, ResponseTrait};
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\Route;

class Auth extends Controller
{
    use ResponseTrait;

    /** @var SAuth */
    private $authService;

    /**
     * Auth constructor.
     * @param SAuth $authService
     */
    public function __construct(SAuth $authService)
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
            return $this->getError($e->getMessage());
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
            return $this->getError($e->getMessage());
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
            return $this->getError($e->getMessage());
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
            return $this->getError($e->getMessage());
        }
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
