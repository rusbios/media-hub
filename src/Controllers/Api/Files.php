<?php

namespace RusBios\MediaHub\Controllers\Api;

use App\Http\Controllers\Controller;
use RusBios\MediaHub\Services\ResponseTrait;
use RusBios\MediaHub\Services\File as SFile;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\{Request, Response};

class Files extends Controller
{
    use ResponseTrait;

    /** @var SFile */
    private $fileService;

    /**
     * Files constructor.
     * @param SFile $fileService
     */
    public function __construct(SFile $fileService)
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
            return $this->getError($e->getMessage());
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
            return $this->getSuccess($this->fileService->create($request));
        } catch (AuthenticationException $e) {
            return $this->getError($e->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return $this->getError($e->getMessage());
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
            return $this->getError($e->getMessage());
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
            return $this->getError($e->getMessage());
        }
    }

    public static function route(): void
    {
        Route::group(['namespace' => 'Api', 'prefix' => 'api/files'], function () {
            Route::get('/', 'Files@store')->name('file-store');
            Route::get('/{id}', 'Files@show')->name('file-show');
            Route::post('/', 'Files@create')->name('file-create');
            Route::delete('/{id}', 'Files@destroy')->name('file-destroy');
        });

        Route::get('/file/{guid}', 'Api\Files@download')->name('file-download');
    }
}
