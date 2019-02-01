<?php

namespace App\Providers;

use App\Handlers\LocatedByUrl;
use App\Handlers\FileSaver;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->when(LocatedByUrl::class)
            ->needs('$allowedContentTypes')
            ->give(
                function () {
                    return array_map(
                        function ($mime) {
                            return "image/$mime";
                        },
                        config('app.allowed_mimes')
                    );
                }
            );

        $this->app->when(FileSaver::class)
            ->needs('$thumbnailSize')
            ->give(env('THUMBNAIL_SIZE'));
    }
}
