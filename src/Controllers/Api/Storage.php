<?php

namespace MediaHub\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Auth\AuthenticationException;
use MediaHub\Services\{ResponseTrait, StorageService};
use Illuminate\Support\Facades\Route;
use Illuminate\Http\{Request, Response};

class Storage extends Controller
{
    use ResponseTrait;

    /** @var StorageService */
    private $storageService;

    /**
     * Storage constructor.
     * @param StorageService $storageService
     */
    public function __construct(StorageService $storageService)
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
            return $this->getError($e->getMessage(), null, $request);
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
            return $this->getError($e->getMessage(), null, $request);
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
            return $this->getError($e->getMessage(), null, $request);
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
            return $this->getError($e->getMessage(), null, $request);
        }
    }

    public static function route()
    {
        Route::group(['namespace' => 'MediaHub\Controllers\Api', 'prefix' => 'api/ftps'], function () {
            Route::get('/', 'Storage@store')->name('api-storage-store');
            Route::get('/{id}', 'Storage@show')->name('api-storage-show');
            Route::post('/', 'Storage@create')->name('api-storage-create');
            Route::put('/{id}', 'Storage@update')->name('api-storage-update');
        });
    }
}
