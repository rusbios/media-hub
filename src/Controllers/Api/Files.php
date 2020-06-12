<?php

namespace MediaHub\Controllers\Api;

use App\Http\Controllers\Controller;
use MediaHub\Services\{ResponseTrait, FileService};
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\{Request, Response};
use Symfony\Component\HttpFoundation\StreamedResponse;

class Files extends Controller
{
    use ResponseTrait;

    /** @var FileService */
    private $fileService;

    /**
     * Files constructor.
     * @param FileService $fileService
     */
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        try {
            return $this->getPagination($this->fileService->list($request));
        } catch (AuthenticationException $e) {
            return $this->getError($e->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return $this->getError($e->getMessage(), null, $request);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function show(Request $request, int $id)
    {
        try {
            return $this->getSuccess($this->fileService->get($request, $id));
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
            return $this->getSuccess($this->fileService->create($request));
        } catch (AuthenticationException $e) {
            return $this->getError($e->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return $this->getError($e->getMessage(), null, $request);
        }
    }

    /**
     * @param Request $request
     * @param string $guid
     * @return StreamedResponse|Response
     */
    public function download(Request $request, string $guid)
    {
        try {
            return $this->fileService->download($request, $guid);
        } catch (AuthenticationException $e) {
            return $this->getError($e->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return $this->getError($e->getMessage(), null, $request);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param string $guid
     * @return Response
     */
    public function destroy(Request $request, string $guid): Response
    {
        try {
            return $this->getSuccess($this->fileService->destroy($request, $guid));
        } catch (AuthenticationException $e) {
            return $this->getError($e->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return $this->getError($e->getMessage(), null, $request);
        }
    }

    public static function route()
    {
        Route::group(['namespace' => 'MediaHub\Controllers\Api', 'prefix' => 'api/files'], function () {
            Route::get('/', 'Files@store')->name('api-file-store');
            Route::get('/{id}', 'Files@show')->name('api-file-show');
            Route::post('/', 'Files@create')->name('api-file-create');
            Route::delete('/{id}', 'Files@destroy')->name('api-file-destroy');
        });

        Route::get('/file/{guid}', 'MediaHub\Controllers\Api\Files@download')->name('file-download');
    }
}
