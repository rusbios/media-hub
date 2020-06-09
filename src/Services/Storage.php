<?php

namespace MediaHub\Services;

use Exception;
use MediaHub\Models\Storage as MStorage;
use MediaHub\Validations\Storage as VStorage;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class Storage
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

        return MStorage::query()
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
        $ftp = MStorage::find($id);

        if (!$ftp || $ftp->user_id !== $user->id) {
            throw new Exception('no access to this item');
        }

        return ['storage' => $ftp->jsonSerialize()];
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
        $ftp = MStorage::find($id);
        $data = VStorage::getValidData($request);

        if (!$data || $ftp->user_id !== $user->id) {
            throw new Exception('incorrect data');
        }

        if (!empty($data['default']) && $data['default'] === true) {
            $this->resetDefaults($user->id);
        }

        $ftp->fill($data)->save();

        return ['storage' => $ftp->jsonSerialize()];
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
        $data = VStorage::getValidData($request);

        if (!$data) {
            throw new Exception('incorrect data');
        }

        $this->resetDefaults($user->id);

        $data = [
                'default' => true,
                'user_id' => $user->id,
            ] + $data;
        $ftp = (new MStorage())->fill($data);
        $ftp->save();

        return ['storage' => $ftp];
    }

    private function resetDefaults(int $userId): void
    {
        MStorage::query()
            ->where('user_id', $userId)
            ->update(['default' => 0]);
    }
}
