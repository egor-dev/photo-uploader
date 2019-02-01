<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PhotoUploadTest extends TestCase
{
    use DatabaseMigrations;

    public function testItUploadsFiles()
    {
        Storage::fake('photos');
        $filesBefore = Storage::disk('photos')->allFiles();

        $response = $this->post(
            '/api/photos/upload',
            [
                'photos' => [
                    UploadedFile::fake()->image('image.jpg', 600, 400),
                ],
            ]
        );
        // checking status
        $response->assertStatus(200);

        $filesAfter = Storage::disk('photos')->allFiles();
        [$originalPath, $thumbnailPath] = array_diff($filesAfter, $filesBefore);

        // checking json response
        $response->assertJsonFragment(
            [
                'message'=>'ok',
                'data'=>
                [
                    [
                        'original_url' => url("storage/$originalPath"),
                        'thumbnail_url' => url("storage/$thumbnailPath")
                    ],
                ],
            ]
        );

        // checking original file sizes
        $original = Storage::disk('photos')->get($originalPath);
        [$originalWidth, $originalHeight] = getimagesizefromstring($original);
        $this->assertEquals(600, $originalWidth);
        $this->assertEquals(400, $originalHeight);

        // checking thumbnail file sizes
        $thumbnail = Storage::disk('photos')->get($thumbnailPath);
        [$thumbnailWidth, $thumbnailHeight] = getimagesizefromstring($thumbnail);
        $this->assertEquals(100, $thumbnailWidth);
        $this->assertEquals(100, $thumbnailHeight);

        // checking database
        $this->assertDatabaseHas(
            'photos',
            [
                'id' => 1,
                'original_path' => $originalPath,
                'thumbnail_path' => $thumbnailPath,
            ]
        );
    }

    public function testItDoesNotAllowLoadingHeavyImages()
    {
        Storage::fake('photos');

        $this->json('POST', '/api/photos/upload', [
            'photos' => [
                UploadedFile::fake()->create('image.jpeg', 10000),
            ], ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('photos', ['id' => 1]);
    }

    public function testItDoesNotAllowLoadingMoreThanFiveImages()
    {
        Storage::fake('photos');

        $this->json('POST', '/api/photos/upload', [
            'photos' => [
                UploadedFile::fake()->image('image.jpg', 600, 400),
                UploadedFile::fake()->image('image.jpg', 600, 400),
                UploadedFile::fake()->image('image.jpg', 600, 400),
                UploadedFile::fake()->image('image.jpg', 600, 400),
                UploadedFile::fake()->image('image.jpg', 600, 400),
                UploadedFile::fake()->image('image.jpg', 600, 400),
            ], ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('photos', ['id' => 1]);
    }

    public function testItUploadsPhotoFromUrl()
    {
        $this->post(
            '/api/photos/upload',
            [
                'urls' => [
                    'https://fcracer.com/wp-content/uploads/GFXR2355-JPEG-blog-thegem-post-thumb-large.jpg',
                ],
            ],
            [
                'Accept' => 'application/json',
            ]
        )->assertStatus(200);
    }

    public function testItDoesNotUploadMoreThanFivePhotosFromUrl()
    {
        $this->post(
            '/api/photos/upload',
            [
                'urls' => [
                    'https://fcracer.com/wp-content/uploads/GFXR2355-JPEG-blog-thegem-post-thumb-large.jpg',
                    'https://fcracer.com/wp-content/uploads/GFXR2355-JPEG-blog-thegem-post-thumb-large.jpg',
                    'https://fcracer.com/wp-content/uploads/GFXR2355-JPEG-blog-thegem-post-thumb-large.jpg',
                    'https://fcracer.com/wp-content/uploads/GFXR2355-JPEG-blog-thegem-post-thumb-large.jpg',
                    'https://fcracer.com/wp-content/uploads/GFXR2355-JPEG-blog-thegem-post-thumb-large.jpg',
                    'https://fcracer.com/wp-content/uploads/GFXR2355-JPEG-blog-thegem-post-thumb-large.jpg',
                ],
            ],
            [
                'Accept' => 'application/json',
            ]
        )->assertStatus(422);
    }

    public function testItDoesNotUploadFromInvalidUrls()
    {
        $this->json(
            'POST',
            '/api/photos/upload',
            [
                'urls' => [
                    'https://image.php',
                ],
            ]
        )->assertStatus(422);
    }

    public function testItUploadsBase64EncodedPhotos()
    {
        Storage::fake('photos');

        $this->json(
            'POST',
            '/api/photos/upload',
            [
                'encoded_photos' => [
                    ['base64' => file_get_contents(base_path('tests/Feature/base64_encoded_photo'))],
                ],
            ]
        )->assertStatus(200);
    }

    public function testItDoesNotAllowToUploadMoreThanFiveBase64EncodedPhotos()
    {
        Storage::fake('photos');

        $this->json(
            'POST',
            '/api/photos/upload',
            [
                'encoded_photos' => [
                    ['base64' => file_get_contents(base_path('tests/Feature/base64_encoded_photo'))],
                    ['base64' => file_get_contents(base_path('tests/Feature/base64_encoded_photo'))],
                    ['base64' => file_get_contents(base_path('tests/Feature/base64_encoded_photo'))],
                    ['base64' => file_get_contents(base_path('tests/Feature/base64_encoded_photo'))],
                    ['base64' => file_get_contents(base_path('tests/Feature/base64_encoded_photo'))],
                    ['base64' => file_get_contents(base_path('tests/Feature/base64_encoded_photo'))],
                ],
            ]
        )->assertStatus(422);
    }
}
