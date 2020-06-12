<?php

namespace MediaHub\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AlbumHasUser
 * @package App\Models
 * @property int $user_id
 * @property int $album_id
 */
class AlbumHasUserModels extends Model
{
    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected $table = 'album_has_users';

    protected $fillable = [
        'user_id',
        'album_id',
    ];
}
