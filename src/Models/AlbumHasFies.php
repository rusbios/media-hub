<?php

namespace MediaHub\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AlbumHasFies
 * @package App\Models
 * @property int $file_id
 * @property int $album_id
 */
class AlbumHasFies extends Model
{
    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected $fillable = [
        'file_id',
        'album_id',
    ];
}
