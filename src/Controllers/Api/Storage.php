<?php

namespace RusBios\MediaHub\Controllers\Api;

use App\Http\Controllers\Controller;
use RusBios\MediaHub\Controllers\ResponseTrait;
use RusBios\MediaHub\Models\Storage as MStorage;
use RusBios\MediaHub\Validations\Storage as VStorage;
use Illuminate\Support\Facades\{Auth, Route};
use Illuminate\Http\{Request, Response};

class Storage extends Controller
{
    use ResponseTrait;

    /**
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        return $this->getPagination(MStorage::query()
            ->where('user_id', Auth::id())
            ->paginate());
    }

    /**
     * @param int $id
     * @return Response
     */
    public function show(int $id): Response
    {
        $ftp = MStorage::find($id);
        if (!$ftp || $ftp->user_id !== Auth::id()) {
            return $this->getError('no access to this item');
        }

        return $this->getSuccess(['ftp' => $ftp]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        $ftp = MStorage::find($id);
        $data = VStorage::getValidData($request);
        if (!$data || $ftp->user_id !== Auth::id()) {
            return $this->getError('incorrect data');
        }

        if (!empty($data['default']) && $data['default'] === true) {
            $this->resetDefaults();
        }

        $ftp->fill($data)->save();

        return $this->getSuccess(['ftp' => $ftp]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $data = VStorage::getValidData($request);
        if (!$data) {
            return $this->getError('incorrect data');
        }

        $this->resetDefaults();

        $data = [
            'default' => true,
            'user_id' => Auth::id(),
        ] + $data;
        $ftp = (new MStorage())->fill($data);
        $ftp->save();

        return $this->getSuccess(['ftp' => $ftp]);
    }

    private function resetDefaults(): void
    {
        MStorage::query()
            ->where('user_id', Auth::id())
            ->update(['default' => 0]);
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
