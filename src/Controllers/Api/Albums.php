<?php

namespace MediaHub\Controllers\Api;

use App\Http\Controllers\Controller;
use MediaHub\Services\{Album as SAlbum, ResponseTrait};
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\{Request, Response};

class Albums extends Controller
{
    use ResponseTrait;

    /** @var SAlbum */
    private $albumService;

    /**
     * Albums constructor.
     * @param SAlbum $albumService
     */
    public function __construct(SAlbum $albumService)
    {
        $this->albumService = $albumService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        try {
            return $this->getPagination($this->albumService->list($request));
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
    public function show(Request $request, int $id): Response
    {
        try {
            return $this->getSuccess($this->albumService->get($request, $id));
        } catch (AuthenticationException $e) {
            return $this->getError($e->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return $this->getError($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        try {
            return $this->getSuccess($this->albumService->update($request, $id));
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
            return $this->getSuccess($this->albumService->create($request));
        } catch (AuthenticationException $e) {
            return $this->getError($e->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return $this->getError($e->getMessage(), null, $request);
        }
    }

    public static function route()
    {
        Route::group(['namespace' => 'MediaHub\Controllers\Api', 'prefix' => 'api/albums'], function () {
            Route::get('/', 'Albums@store')->name('album-store');
            Route::get('/{id}', 'Albums@show')->name('album-show');
            Route::post('/', 'Albums@create')->name('album-create');
            Route::put('/{id}', 'Albums@update')->name('album-update');
        });
    }
}
