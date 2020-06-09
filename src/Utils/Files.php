<?php

namespace MediaHub\Utils;

use MediaHub\Models\{File, User};
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Config, Storage};

class Files
{
    /**
     * @param UploadedFile[] $upFiles
     * @param User $user
     * @param string|null $ip
     * @param int|null $ftp_id
     * @return File[]
     * @throws Exception
     */
    public static function createFile(array $upFiles, User $user, ?string $ip, ?int $ftp_id): iterable
    {
        if (!$ftp_id) {
            $ftp_id = User::find($user->id)->getDefaultFTP()->id;
        }

        $files = [];

        foreach ($upFiles as $upFile) {
            $file = new File();
            $file->fill([
                'guid' => MbString::makeGUID($ip),
                'hash' => md5_file($upFile->path()),
                'ftp_id' => $ftp_id,
                'name' => $upFile->getClientOriginalName(),
                'mime_type' => $upFile->getMimeType(),
                'size' => $upFile->getSize(),
                'user_id' => $user->id,
                'path' => Dirs::getPath(ip2long($ip)),
                'status' => File::STATUS_TEMP,
            ]);

            if ($clone = File::query()->where('hash', $file->hash)->first()) {
                //TODO чтото зделать, наверное
            }

            if (!Storage::disk('local')->put($file->path.$file->guid, file_get_contents($upFile->path()))) {
                $file->forceDelete();
                throw new Exception('failed to move file');
            }

            $file->save();

            $files[] = $file;
        }

        return $files;
    }

    /**
     * @param File $file
     * @throws Exception
     */
    public static function uploadFile(File $file): void
    {
        $file->status = File::STATUS_LOADING;
        $file->save();

        $file->preview = self::createPreview($file);

        $ftp = $file->getFtp();
        Config::set('filesystems.disks.ftp.host', $ftp->host);
        Config::set('filesystems.disks.ftp.username', $ftp->login);
        Config::set('filesystems.disks.ftp.password', $ftp->password);
        Config::set('filesystems.disks.ftp.port', $ftp->port);
        Config::set('filesystems.disks.ftp.ssl', false);

        try {
            Storage::disk('ftp')->put(
                $file->path . $file->guid,
                Storage::disk('local')->get($file->path . $file->guid)
            );

            Storage::disk('local')->delete($file->path . $file->guid);
        } catch (Exception $e) {
            $file->status = File::STATUS_ERROR;
            $file->save();
            throw $e;
        }

        $file->status = File::STATUS_READY;
        $file->save();
    }

    /**
     * @param File $file
     * @return string|null
     */
    public static function createPreview(File $file): ?string
    {
//        $image = Image::make(storage_path('files/' . $file->path . $file->guid));
//        dd($image->resize(400, 400)->getEncoded());
        switch ($file->mime_type) {
            default:
                return null;
        }
    }
}
