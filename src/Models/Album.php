<?php

namespace MediaHub\Models;

use DateTime;
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
 * @method static Album find(int $id)
 */
class Album extends Model
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
     * @return HasOne|User
     */
    public function getUser()
    {
        return $this->hasOne(User::class, 'user_id', 'id');
    }

    /**
     * @param bool $isShort
     * @return array
     */
    public function jsonSerialize($isShort = false): array
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
        ];
    }
}
