<?php

namespace RusBios\MediaHub\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use RusBios\MediaHub\Services\{ResponseTrait, Storage as SStorage};
use Illuminate\Support\Facades\Route;
use Illuminate\Http\{Request, Response};

class Storage extends Controller
{
    use ResponseTrait;

    /** @var SStorage */
    private $storageService;

    public function __construct(SStorage $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        try {
            return $this->getPagination($this->storageService->list($request));
        } catch (AuthenticationException $e) {
            return $this->getError($e->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return $this->getError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function show(Request $request, int $id): Response
    {
        try {
            return $this->getSuccess($this->storageService->get($request, $id));
        } catch (AuthenticationException $e) {
            return $this->getError($e->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return $this->getError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        try {
            return $this->getSuccess($this->storageService->update($request, $id));
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
    public function create(Request $request): Response
    {
        try {
            return $this->getSuccess($this->storageService->create($request));
        } catch (AuthenticationException $e) {
            return $this->getError($e->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return $this->getError($e->getMessage());
        }
    }

    public static function route(): void
    {
        Route::group(['namespace' => 'Api', 'prefix' => 'api/ftps'], function () {
            Route::get('/', 'Ftps@store')->name('ftp-store');
            Route::get('/{id}', 'Ftps@show')->name('ftp-show');
            Route::post('/', 'Ftps@create')->name('ftp-create');
            Route::put('/{id}', 'Ftps@update')->name('ftp-update');
        });
    }
}
