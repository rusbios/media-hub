<?php

namespace MediaHub\Services;

use Exception;
use Illuminate\Support\Facades\Config;
use MediaHub\Models\File as MFile;
use MediaHub\Utils\Files as UFiles;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class File
{
    use ResponseTrait;

    /**
     * @param Request $request
     *
     * @return LengthAwarePaginator
     *
     * @throws Exception
     * @throws AuthenticationException
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $user = $this->decodeToken($request);

        return File::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate();
    }

    /**
     * @param Request $request
     * @param string $guid
     *
     * @return array
     *
     * @throws Exception
     * @throws AuthenticationException
     */
    public function get(Request $request, string $guid): array
    {
        $user = $this->decodeToken($request);

        $file = MFile::getByGuid($guid);
        if (!$file || $file->user_id !== $user->id) {
            throw new Exception('no access to this item');
        }

        return ['file' => $file->jsonSerialize()];
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

        $files = UFiles::createFile(
            $request->file('files'),
            $user,
            $request->getClientIp(),
            $request->get('storage_id', $user->getDefaultStorage()->id),
            $request->get('album_id', $user->getDefaultAlbum()->id)
        );

        return ['file' => $files];
    }

    /**
     * @param Request $request
     * @param string $guid
     *
     * @return StreamedResponse
     *
     * @throws Exception
     * @throws AuthenticationException
     */
    public function download(Request $request, string $guid): StreamedResponse
    {
        $user = $this->decodeToken($request);
        $file = MFile::getByGuid($guid);

        $usersId = $file->getUserAccess();
        $usersId[] = $file->user_id;

        if (!in_array($user->id, $usersId)) {
            throw new Exception('insufficient access rights');
        }

        $storage = $file->getStorage();
        Config::set('filesystems.disks.ftp.host', $storage->host);
        Config::set('filesystems.disks.ftp.username', $storage->login);
        Config::set('filesystems.disks.ftp.password', $storage->password);
        Config::set('filesystems.disks.ftp.port', $storage->port);
        Config::set('filesystems.disks.ftp.ssl', false);

        return response()->streamDownload(function () use ($file) {
            return Storage::disk($file->status === MFile::STATUS_READY ? 'ftp' : 'local')->get($file->path . $file->guid);
        },
            $file->name,
            ['Content-Type' => $file->mime_type]
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param static $guid
     *
     * @return array
     *
     * @throws Exception
     * @throws AuthenticationException
     */
    public function destroy(Request $request, string $guid): array
    {
        $user = $this->decodeToken($request);

        $file = MFile::getByGuid($guid);

        if ($file->user_id != $user->id) {
            throw new AuthenticationException();
        }

        $file->delete();
        $file->save();


        return [];
    }
}
