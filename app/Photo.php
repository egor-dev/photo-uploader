<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Class Photo.
 *
 * @property string $original_path
 * @property string $thumbnail_path
 *
 * @package App
 */
class Photo extends Model
{
    public function getOriginalUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->original_path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->thumbnail_path);
    }
}
