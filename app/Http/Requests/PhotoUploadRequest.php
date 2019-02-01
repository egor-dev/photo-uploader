<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PhotoUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $allowedMimes = config('app.allowed_mimes');
        $allowedMimesCommaImploded = implode(',', $allowedMimes);
        $allowedMimesStickImploded = implode('|', config('app.allowed_mimes'));

        $megabytes = env('MAX_PHOTO_SIZE_MEGABYTES');
        $maxFileSize = 1024 * $megabytes;
        $maxEncodedFileSize = 1.37 * 1048576 * $megabytes;
        $maxFilesPerRequest = env('MAX_FILES_PER_REQUEST');
        /**
         * Explanation:
         * 11492393 bytes = 8388608 bytes * 1,37
         * 8388608 b = 8 Mb - max image size for uploading
         * size of original image increases 1.37 times after base64 encoding
         */

        return [
            'photos' => "required_without_all:urls,encoded_photos|array|min:1|max:$maxFilesPerRequest",
            'photos.*' => "image|max:$maxFileSize|mimes:$allowedMimesCommaImploded",

            'urls' => "required_without_all:photos,encoded_photos|array|min:1|max:$maxFilesPerRequest",
            'urls.*' => [
                'url',
                "regex:/.($allowedMimesStickImploded)$/i"
            ],

            'encoded_photos' => 'required_without_all:photos,urls|array|min:1|max:5',
            'encoded_photos.*.base64' => [
                'regex:/^data:image\/(' . $allowedMimesStickImploded . ');base64,.+$/i',
                //'string',
                "max:$maxEncodedFileSize",
            ],
        ];
    }
}
