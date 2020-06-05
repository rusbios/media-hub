<?php

namespace RusBios\MediaHub\Models;

use DateTime;

/**
 * @method static User find(int $id)
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property DateTime|null $email_verified_at
 * @property string $password
 * @property string $remember_token
 * @property DateTime $created_at
 * @property DateTime $updated_at
 */
class User extends \App\Models\User
{
    /**
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @return Storage[]
     */
    public function getStorage(): iterable
    {
        return $this->hasMany(Storage::class, 'id', 'user_id');
    }

    /**
     * @return Album[]
     */
    public function getAlbum(): iterable
    {
        return $this->hasMany(Album::class, 'id', 'user_id');
    }

    /**
     * @return Storage|null
     */
    public function getDefaultStorage(): Storage
    {
        return $this->hasMany(Storage::class, 'user_id', 'id')
            ->where('default', 1)
            ->first();
    }

    /**
     * @return Album|null
     */
    public function getDefaultAlbum(): Album
    {
        return $this->hasMany(Album::class, 'user_id', 'id')
            ->where('default', 1)
            ->first();
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
                'email' => $this->email,
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_valid_email' => $this->email_verified_at != null,
            'created_at' => $this->created_at->format('Y-m-d H.i.s'),
        ];
    }
}
