<?php

namespace RusBios\MediaHub\Controllers\Api;

use App\Http\Controllers\Controller;
use RusBios\MediaHub\Models\{Album, AlbumHasUser};
use RusBios\MediaHub\Utils\MbString;
use RusBios\MediaHub\Validations\Album as VAlbum;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\Auth;
use RusBios\MediaHub\Controllers\ResponseTrait;

class Albums extends Controller
{
    use ResponseTrait;

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        return $this->getPagination(Album::query()
            ->where('user_id', Auth::id())
            ->paginate());
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show(int $id): Response
    {
        $album = Album::find($id);
        if (!$album || $album->user_id !== Auth::id()) {
            return $this->getError('no access to this item');
        }

        return $this->getSuccess(['album' => $album]);
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
        $album = Album::find($id);
        $data = VAlbum::getValidData($request);
        if (!$data || $album->user_id !== Auth::id()) {
            return $this->getError('incorrect data');
        }

        $data = ['access' => $album->access] + $data;

        $album->fill($data)->save();

        $this->closeAccessToUsers($album->id, $request->get('close_user', []));
        $this->openAccessToUsers($album, $request->get('open_user', []));

        return $this->getSuccess(['album' => $album]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $data = VAlbum::getValidData($request);

        if (!$data) {
            return $this->getError('incorrect data');
        }

        $data = [
                'user_id' => Auth::id(),
                'url' => MbString::toUrl($data['name']),
                'access' => Album::ACCESS_PRIVATE,
            ] + $data;

        if (Album::query()
            ->where('url', $data['url'])
            ->where('user_id', $data['user_id'])
            ->first()) {
            $data['url'] .= '_' . strtolower(MbString::generateSymbols(3));
        }
        $album = (new Album())->fill($data);
        $album->save();

        return $this->getSuccess(['album' => $album]);
    }

    /**
     * @param Album $album
     * @param int[]|null $userIds
     */
    protected function openAccessToUsers(Album $album, ?array $userIds): void
    {
        if ($album->access < Album::ACCESS_PROTECT) {
            $album->access = Album::ACCESS_PROTECT;
            $album->save();
        }

        if ($userIds) {
            foreach ($userIds as $userId) {
                (new AlbumHasUser())->fill([
                    'user_id' => $userId,
                    'album_id' => $album->id,
                ])->save();
            }
        }
    }

    /**
     * @param int $albumId
     * @param int[]|null $userIds
     */
    protected function closeAccessToUsers(int $albumId, ?array $userIds): void
    {
        if ($userIds) {
            AlbumHasUser::query()
                ->where('album_id', $albumId)
                ->whereIn('user_id', $userIds)
                ->delete();
        }
    }

    public static function route(): void
    {
        Route::group(['namespace' => 'Api', 'prefix' => 'api/albums'], function () {
            Route::get('/', 'Albums@store')->name('album-store');
            Route::get('/{id}', 'Albums@show')->name('album-show');
            Route::post('/', 'Albums@create')->name('album-create');
            Route::put('/{id}', 'Albums@update')->name('album-update');
        });
    }
}
