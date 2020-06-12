<?php

namespace MediaHub\Services;

use Exception;
use MediaHub\Models\{AlbumModels, AlbumHasUserModels};
use MediaHub\Utils\MbString;
use MediaHub\Validations\AlbumValidation;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AlbumService
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

        return AlbumModels::getStory($user->id, $request->get('page'));
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

        $album = AlbumModels::find($id);
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

        $album = AlbumModels::find($id);
        $data = AlbumValidation::getValidData($request);
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

        $data = AlbumValidation::getValidData($request);

        if (!$data) {
            throw new Exception('incorrect data');
        }

        $data = [
                'user_id' => $user->id,
                'url' => MbString::toUrl($data['name']),
                'access' => AlbumModels::ACCESS_PRIVATE,
            ] + $data;

        if (AlbumModels::query()
            ->where('url', $data['url'])
            ->where('user_id', $data['user_id'])
            ->first()) {
            $data['url'] .= '_' . strtolower(MbString::generateSymbols(3));
        }
        $album = (new AlbumModels())->fill($data);
        $album->save();

        return ['album' => $album->jsonSerialize()];
    }

    /**
     * @param AlbumModels $album
     * @param int[]|null $userIds
     */
    protected function openAccessToUsers(AlbumModels $album, ?array $userIds): void
    {
        if ($album->access < AlbumModels::ACCESS_PROTECT) {
            $album->access = AlbumModels::ACCESS_PROTECT;
            $album->save();
        }

        if ($userIds) {
            foreach ($userIds as $userId) {
                (new AlbumHasUserModels())->fill([
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
            AlbumHasUserModels::query()
                ->where('album_id', $albumId)
                ->whereIn('user_id', $userIds)
                ->delete();
        }
    }
}
