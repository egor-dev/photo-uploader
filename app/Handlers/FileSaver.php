<?php

namespace App\Handlers;

use App\Photo;
use Intervention\Image\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Saves file (with thumbnail) to storage, persists to database.
 *
 * @package App\Acme
 */
class FileSaver
{
    /**
     * @var int
     */
    private $thumbnailSize;

    /**
     * FileSaver constructor.
     *
     * @param int $thumbnailSize
     */
    public function __construct(int $thumbnailSize)
    {
        $this->thumbnailSize = $thumbnailSize;
    }

    /**
     * @param string|UploadedFile $contents
     * @param string $extension
     *
     * @return Photo
     */
    public function save($contents, string $extension): Photo
    {
        $filename = implode('.', [sha1(microtime()), $extension]);
        $subDir = mb_strcut($filename, 0, 3);

        /** @var Image $original */
        $original = \Intervention\Image\Facades\Image::make($contents);
        $originalPath = "originals/$subDir/$filename";
        Storage::disk('photos')->put($originalPath, (string)$original->encode());

        $thumbnail = $original->fit($this->thumbnailSize, $this->thumbnailSize);
        $thumbnailPath = "thumbnails/$subDir/$filename";
        Storage::disk('photos')->put($thumbnailPath, (string)$thumbnail->encode());

        $photo = new Photo();
        $photo->original_path = $originalPath;
        $photo->thumbnail_path = $thumbnailPath;
        $photo->save();

        return $photo;
    }
}
