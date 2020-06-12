<?php

namespace MediaHub\Utils;

use MediaHub\Models\{AlbumHasFiesModels, FileModels, UserModels};
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Config, Storage};

class Files
{
    /**
     * @param UploadedFile[] $upFiles
     * @param UserModels $user
     * @param string|null $ip
     * @param int|null $storage_id
     * @param int|null $album_id
     * @return FileModels[]
     * @throws Exception
     */
    public static function createFile(array $upFiles, UserModels $user, ?string $ip, ?int $storage_id, ?int $album_id): iterable
    {
        if (!$storage_id) {
            $storage_id = $user->getDefaultStorage()->id;
        }

        if (!$album_id) {
            $album_id = $user->getDefaultAlbum()->id;
        }

        $files = [];

        foreach ($upFiles as $upFile) {
            $file = new FileModels();
            $file->fill([
                'guid' => MbString::makeGUID($ip),
                'hash' => md5_file($upFile->path()),
                'storage_id' => $storage_id,
                'name' => $upFile->getClientOriginalName(),
                'mime_type' => $upFile->getMimeType(),
                'size' => $upFile->getSize(),
                'user_id' => $user->id,
                'path' => Dirs::getPath(ip2long($ip)),
                'status' => FileModels::STATUS_TEMP,
            ]);

            if ($clone = FileModels::query()->where('hash', $file->hash)->first()) {
                //TODO чтото зделать, наверное
            }

            if (!Storage::disk('local')->put($file->path.$file->guid, file_get_contents($upFile->path()))) {
                $file->forceDelete();
                throw new Exception('failed to move file');
            }

            $file->save();

            (new AlbumHasFiesModels())->fill([
                'file_id' => $file->id,
                'album_id' => $album_id,
            ])->save();

            $files[] = $file;
        }

        return $files;
    }

    /**
     * @param FileModels $file
     * @throws Exception
     */
    public static function uploadFile(FileModels $file): void
    {
        $file->status = FileModels::STATUS_LOADING;
        $file->save();

        $file->preview = self::createPreview($file);

        $storage = $file->getStorage();

        if ($storage->type != 'ftp') {
            $file->status = FileModels::STATUS_ERROR;
            $file->save();
            return;
        }

        Config::set('filesystems.disks.ftp.host', $storage->host);
        Config::set('filesystems.disks.ftp.username', $storage->login);
        Config::set('filesystems.disks.ftp.password', $storage->password);
        Config::set('filesystems.disks.ftp.port', $storage->port);
        Config::set('filesystems.disks.ftp.ssl', false);

        try {
            Storage::disk('ftp')->put(
                $file->path . $file->guid,
                Storage::disk('local')->get($file->path . $file->guid)
            );

            Storage::disk('local')->delete($file->path . $file->guid);
        } catch (Exception $e) {
            $file->status = FileModels::STATUS_ERROR;
            $file->save();
            throw $e;
        }

        $file->status = FileModels::STATUS_READY;
        $file->save();
    }

    /**
     * @param FileModels $file
     * @return string|null
     */
    public static function createPreview(FileModels $file): ?string
    {
//        $image = Image::make(storage_path('files/' . $file->path . $file->guid));
//        dd($image->resize(400, 400)->getEncoded());
        switch ($file->mime_type) {
            default:
                return null;
        }
    }
}
