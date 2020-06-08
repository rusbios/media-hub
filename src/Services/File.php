<?php

namespace RusBios\MediaHub\Services;

use Exception;
use RusBios\MediaHub\Models\File as MFile;
use RusBios\MediaHub\Utils\Files as UFiles;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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
            $request->allFiles(),
            $user,
            $request->getClientIp(),
            $request->get('ftp_id', $user->getDefaultStorage()->id)
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

        $ftp = $file->getFtp();
        Config::set('filesystems.disks.ftp.host', $ftp->host);
        Config::set('filesystems.disks.ftp.username', $ftp->login);
        Config::set('filesystems.disks.ftp.password', $ftp->password);
        Config::set('filesystems.disks.ftp.port', $ftp->port);
        Config::set('filesystems.disks.ftp.ssl', false);

        return response()->streamDownload(function () use ($file) {
            return Storage::disk($file->status === File::STATUS_READY ? 'ftp' : 'local')->get($file->path . $file->guid);
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


        return $this->getSuccess([]);
    }
}
