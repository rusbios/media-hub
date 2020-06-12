<?php

namespace MediaHub\Models;

use DateTime;

/**
 * @method static UserModels find(int $id)
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
class UserModels extends \App\User
{
    protected $table = 'users';

    /**
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @return StorageModels[]
     */
    public function getStorage(): iterable
    {
        return $this->hasMany(StorageModels::class, 'id', 'user_id');
    }

    /**
     * @return AlbumModels[]
     */
    public function getAlbum(): iterable
    {
        return $this->hasMany(AlbumModels::class, 'id', 'user_id');
    }

    /**
     * @return StorageModels|null
     */
    public function getDefaultStorage(): StorageModels
    {
        return $this->hasMany(StorageModels::class, 'user_id', 'id')
            ->where('default', 1)
            ->first();
    }

    /**
     * @return AlbumModels|null
     */
    public function getDefaultAlbum(): AlbumModels
    {
        $album = $this->hasMany(AlbumModels::class, 'user_id', 'id')
            ->where('default', 1)
            ->first();

        if (!$album) {
            $album = new AlbumModels();
            $album->fill([
                'user_id' => $this->id,
                'url' => 'default',
                'access' => AlbumModels::ACCESS_PRIVATE,
                'name' => 'default',
            ]);
            $album->save();
        }

        return $album;
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
