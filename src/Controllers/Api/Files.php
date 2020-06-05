<?php

namespace RusBios\MediaHub\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{File, User};
use RusBios\MediaHub\Controllers\ResponseTrait;
use RusBios\MediaHub\Utils\Files as UFiles;
use Exception;
use Illuminate\Support\Facades\{Auth, Config, Route, Storage};
use Illuminate\Http\{Request, Response};
use Symfony\Component\HttpFoundation\StreamedResponse;

class Files extends Controller
{
    use ResponseTrait;

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        return $this->getPagination(File::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate());
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show(int $id)
    {
        $file = File::find($id);
        if (!$file || $file->user_id !== Auth::id()) {
            return $this->getError('no access to this item');
        }

        return $this->getSuccess(['file' => $file]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $user = User::find(Auth::id());

        try {
            $files = UFiles::createFile(
                $request->allFiles(),
                $user,
                $request->getClientIp(),
                $request->get('ftp_id', $user->getDefaultFTP()->id)
            );
        } catch (Exception $e) {
            return $this->getError($e->getMessage());
        }

        return $this->getSuccess(['file' => $files]);
    }

    /**
     * @param string $guid
     * @return StreamedResponse|Response
     */
    public function download(string $guid)
    {
        /** @var File $file */
        $file = File::query()
            ->where('guid', $guid)
            ->first();

        $usersId = $file->getUserAccess();
        $usersId[] = $file->user_id;

        if (!in_array(Auth::id(), $usersId)) {
            return $this->getError('insufficient access rights', Response::HTTP_FORBIDDEN);
        }

        $ftp = $file->getFtp();
        Config::set('filesystems.disks.ftp.host', $ftp->host);
        Config::set('filesystems.disks.ftp.username', $ftp->login);
        Config::set('filesystems.disks.ftp.password', $ftp->password);
        Config::set('filesystems.disks.ftp.port', $ftp->port);
        Config::set('filesystems.disks.ftp.ssl', false);

        return response()->streamDownload(function () use ($file) {
                echo Storage::disk($file->status === File::STATUS_READY ? 'ftp' : 'local')->get($file->path . $file->guid);
            },
            $file->name,
            ['Content-Type' => $file->mime_type]
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param File $file
     * @return Response
     */
    public function destroy(File $file): Response
    {
        try {
            $file->delete();
            $file->save();
        } catch (Exception $e) {
            return $this->getError($e->getMessage());
        }

        return $this->getSuccess([]);
    }

    public static function route(): void
    {
        Route::group(['namespace' => 'Api', 'prefix' => 'api/files'], function () {
            Route::get('/', 'Files@store')->name('file-store');
            Route::get('/{id}', 'Files@show')->name('file-show');
            Route::post('/', 'Files@create')->name('file-create');
            Route::delete('/{id}', 'Files@destroy')->name('file-destroy');
        });

        Route::get('/file/{guid}', 'Api\Files@download')->name('file-download');
    }
}
