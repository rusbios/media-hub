<?php

namespace RusBios\MediaHub\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Ftp
 * @package App\Models
 * @property int $id
 * @property int $user_id
 * @property string $host
 * @property int $port
 * @property string $login
 * @property string $password
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property bool $default
 *
 * @method static Storage find(int $id)
 */
class Storage extends Model
{
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
     * @param bool $isShort
     * @return array
     */
    public function jsonSerialize($isShort = false): array
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
