<?php

namespace App\Handlers;

use Illuminate\Support\Collection;

/**
 * Processes base 64 encoded uploaded images.
 *
 * @package App\Acme
 */
class Base64Encoded
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
     * @param array $encodedPhotos
     *
     * @return Collection
     */
    public function handle(array $encodedPhotos): Collection
    {
        $photos = collect();
        foreach ($encodedPhotos as $photo) {
            $photos->push(
                $this->fileSaver->save(
                    base64_decode($this->getEncodedString($photo['base64'])),
                    $this->getExtension($photo['base64'])
                )
            );
        }

        return $photos;
    }

    /**
     * @param string $base64
     * @return string
     */
    private function getExtension(string $base64): string
    {
        $explode = explode(';base64,', $base64);

        return explode('/', $explode[0])[1];
    }

    /**
     * @param string $base64
     * @return string
     */
    private function getEncodedString(string $base64): string
    {
        $explode = explode(';base64,', $base64);

        return $explode[1];
    }
}
