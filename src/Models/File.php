<?php

namespace RusBios\MediaHub\Models;

use DateTime;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

/**
 * Class File
 * @package App\Models
 * @property int $id
 * @property string $guid
 * @property string $hash
 * @property int $storage_id
 * @property string $path
 * @property string $name
 * @property string $mime_type
 * @property string $preview
 * @property int $size
 * @property int $user_id
 * @property int $status
 * @property DateTime created_at
 * @property DateTime updated_at
 * @property DateTime deleted_at
 *
 * @method static File find(int $id)
 */
class File extends Model
{
    use SoftDeletes;

    public const STATUS_ERROR = 0;
    public const STATUS_TEMP = 1;
    public const STATUS_LOADING = 2;
    public const STATUS_READY = 3;

    public const STATUSES = [
        self::STATUS_ERROR,
        self::STATUS_TEMP,
        self::STATUS_LOADING,
        self::STATUS_READY,
    ];

    protected $fillable = [
        'guid',
        'hash',
        'storage_id',
        'name',
        'mime_type',
        'size',
        'user_id',
        'path',
        'status',
    ];

    /**
     * @return int[]
     */
    public function getUserAccess(): array
    {
        return AlbumHasUser::query()
            ->whereIn('album_id', AlbumHasFies::query()->where('file_id', $this->id)
            ->pluck('album_id')
            ->all())->pluck('user_id')->all();
    }

    /**
     * @return Storage
     */
    public function getStorage()
    {
        return Storage::find($this->storage_id);
    }
}
