<?php

namespace App\Http\Controllers\Api;

use App\Handlers\Uploaded;
use App\Handlers\LocatedByUrl;
use App\Handlers\Base64Encoded;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\PhotoResource;
use App\Http\Requests\PhotoUploadRequest;

/**
 * Photo uploading controller.
 *
 * @package App\Http\Controllers\Api
 */
class PhotoUploadController extends Controller
{
    /**
     * @var LocatedByUrl
     */
    private $locatedByUrl;
    /**
     * @var Uploaded
     */
    private $uploaded;
    /**
     * @var Base64Encoded
     */
    private $base64Encoded;

    public function __construct(
        LocatedByUrl $locatedByUrl,
        Uploaded $uploaded,
        Base64Encoded $base64Encoded
    ) {
        $this->locatedByUrl = $locatedByUrl;
        $this->uploaded = $uploaded;
        $this->base64Encoded = $base64Encoded;
    }

    /**
     * Upload image.
     *
     * @param PhotoUploadRequest $request
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return JsonResponse
     */
    public function upload(PhotoUploadRequest $request): JsonResponse
    {
        $photos = null;
        $errors = collect();
        if ($request->isJson()) {
            $photos = $this->base64Encoded->handle($request->json()->get('encoded_photos'));
        } elseif ($request->filled('urls')) {
            [$photos, $errors] = $this->locatedByUrl->handle($request->input('urls'));
        } else {
            [$photos, $errors] = $this->uploaded->handle($request->file('photos'));
        }

        return \response()->json([
            'message' => 'ok',
            'data' => PhotoResource::collection($photos),
            'errors' => $errors,
        ]);
    }
}
