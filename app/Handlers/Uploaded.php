<?php

namespace App\Handlers;

use Illuminate\Http\UploadedFile;

/**
 * Just saves uploaded files.
 *
 * @package App\Acme
 */
class Uploaded
{
    /**
     * @var FileSaver
     */
    private $fileSaver;

    public function __construct(FileSaver $fileSaver)
    {
        $this->fileSaver = $fileSaver;
    }

    /**
     * @param UploadedFile[] $files
     *
     * @return array
     */
    public function handle($files): array
    {
        $photos = collect();
        $errors = collect();
        foreach ($files as $file) {
            if (! $file->isValid()) {
                $errors->push("Failed to upload file {$file->getClientOriginalName()} due to HTTP error.");

                continue;
            }
            $photos->push(
                $this->fileSaver->save($file, $file->extension())
            );
        }

        return [$photos, $errors];
    }
}
