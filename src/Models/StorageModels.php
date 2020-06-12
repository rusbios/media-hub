<?php

namespace MediaHub\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class Ftp
 * @package App\Models
 * @property int $id
 * @property int $user_id
 * @property string $host
 * @property string $type
 * @property int $port
 * @property string $login
 * @property string $password
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property bool $default
 *
 * @method static StorageModels find(int $id)
 */
class StorageModels extends Model
{
    protected $table = 'storage';

    protected $fillable = [
        'password',
        'port',
        'user_id',
        'host',
        'default',
        'login',
    ];

    protected $hidden = [
        'password',
        'user_id',
    ];

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
                'default' => $this->default,
                'host' => $this->host,
                'port' => $this->port,
            ];
        }

        return [
            'id' => $this->id,
            'default' => $this->default,
            'host' => $this->host,
            'port' => $this->port,
            'login' => $this->login,
            'password' => $this->password,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
