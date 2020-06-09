<?php

namespace MediaHub\Services;

use Exception;
use MediaHub\Models\{Album as MAlbum, AlbumHasUser};
use MediaHub\Utils\MbString;
use MediaHub\Validations\Album as VAlbum;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class Album
{
    use ResponseTrait;

    /**
     * @param Request $request
     *
     * @return LengthAwarePaginator
     *
     * @throws AuthenticationException
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $user = $this->decodeToken($request);

        return MAlbum::query()
            ->where('user_id', $user->id)
            ->paginate();
    }

    /**
     * @param Request $request
     * @param int $id
     *
     * @return array
     *
     * @throws Exception
     * @throws AuthenticationException
     */
    public function get(Request $request, int $id): array
    {
        $user = $this->decodeToken($request);

        $album = MAlbum::find($id);
        if (!$album || $album->user_id !== $user->id) {
            throw new Exception('no access to this item');
        }

        return ['album' => $album->jsonSerialize()];
    }

    /**
     * @param Request $request
     * @param int $id
     *
     * @return array
     *
     * @throws Exception
     * @throws AuthenticationException
     */
    public function update(Request $request, int $id): array
    {
        $user = $this->decodeToken($request);

        $album = MAlbum::find($id);
        $data = VAlbum::getValidData($request);
        if (!$data || $album->user_id !== $user->id) {
            throw new Exception('incorrect data');
        }

        $data = ['access' => $album->access] + $data;

        $album->fill($data)->save();

        $this->closeAccessToUsers($album->id, $request->get('close_user', []));
        $this->openAccessToUsers($album, $request->get('open_user', []));

        return ['album' => $album->jsonSerialize()];
    }

    /**
     * @param Request $request
     *
     * @return array
     *
     * @throws Exception
     * @throws AuthenticationException
     */
    public function create(Request $request): array
    {
        $user = $this->decodeToken($request);

        $data = VAlbum::getValidData($request);

        if (!$data) {
            throw new Exception('incorrect data');
        }

        $data = [
                'user_id' => $user->id,
                'url' => MbString::toUrl($data['name']),
                'access' => MAlbum::ACCESS_PRIVATE,
            ] + $data;

        if (MAlbum::query()
            ->where('url', $data['url'])
            ->where('user_id', $data['user_id'])
            ->first()) {
            $data['url'] .= '_' . strtolower(MbString::generateSymbols(3));
        }
        $album = (new MAlbum())->fill($data);
        $album->save();

        return ['album' => $album->jsonSerialize()];
    }

    /**
     * @param MAlbum $album
     * @param int[]|null $userIds
     */
    protected function openAccessToUsers(MAlbum $album, ?array $userIds): void
    {
        if ($album->access < MAlbum::ACCESS_PROTECT) {
            $album->access = MAlbum::ACCESS_PROTECT;
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
}
