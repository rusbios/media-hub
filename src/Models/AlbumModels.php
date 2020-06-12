<?php

namespace MediaHub\Models;

use DateTime;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne, SoftDeletes};

/**
 * Class Album
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property int $user_id
 * @property bool $default
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property DateTime $deleted_at
 * @property int $access
 * @property string $url
 *
 * @method static AlbumModels find(int $id)
 */
class AlbumModels extends Model
{
    use SoftDeletes;

    public const ACCESS_PRIVATE = 1;
    public const ACCESS_PROTECT = 2;
    public const ACCESS_PUBLIC = 3;

    public const ACCESSES = [
        self::ACCESS_PRIVATE,
        self::ACCESS_PROTECT,
        self::ACCESS_PUBLIC,
    ];

    protected $table = 'albums';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'access',
        'url'
    ];

    /**
     * @return HasOne|UserModels
     */
    public function getUser()
    {
        return $this->hasOne(UserModels::class, 'user_id', 'id');
    }

    /**
     * @return int[]
     */
    public function getAccessUsers(): array
    {
        return $this->hasMany(AlbumHasUserModels::class, 'id', 'album_id')
            ->pluck('user_id')->all();
    }

    /**
     * @param int $userId
     * @param int $page
     * @param int|null $prePage
     * @return LengthAwarePaginator
     */
    public static function getStory(int $userId, int $page = 1, int $prePage = null): LengthAwarePaginator
    {
        return self::query()
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->paginate($prePage, ['*'], 'page', $page);
    }

    /**
     * @param bool $isShort
     * @return array
     */
    public function jsonSerialize($isShort = true): array
    {
        if ($isShort) {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'url' => $this->url,
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'access' => $this->access,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'access_users' => $this->getAccessUsers(),
        ];
    }
}
