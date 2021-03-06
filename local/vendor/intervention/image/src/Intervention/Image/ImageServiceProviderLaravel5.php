<?php

namespace Intervention\Image;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Response as IlluminateResponse;

class ImageServiceProviderLaravel5 extends ServiceProvider
{
    /**
     * Determines if Intervention Imagecache is installed
     *
     * @return boolean
     */
    private function cacheIsInstalled()
    {
        return class_exists('Intervention\\Image\\ImageCache');
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes(array(
            __DIR__.'/../../config/config.php' => config_path('image.php')
        ));

        // setup intervention/imagecache if package is installed
        $this->cacheIsInstalled() ? $this->bootstrapImageCache() : null;
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        // merge default config
        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php',
            'image'
        );

        // create image
        $app['image'] = $app->share(function ($app) {
            return new ImageManager($app['config']->get('image'));
        });
    }

    /**
     * Bootstrap imagecache
     *
     * @return void
     */
    private function bootstrapImageCache()
    {
        $app = $this->app;
        $config = __DIR__.'/../../../../imagecache/src/config/config.php';

        $this->publishes(array(
            $config => config_path('imagecache.php')
        ));

        // merge default config
        $this->mergeConfigFrom(
            $config,
            'imagecache'
        );

        // imagecache route
        if (is_string(config('imagecache.route'))) {

            $filename_pattern = '[ \w\\.\\/\\-]+';

            // route to access original file
            $app['router']->get(
                config('imagecache.route').'/original/{filename}',
                'Intervention\Image\ImageCacheController@getOriginal'
            )->where(array('filename' => $filename_pattern));

            // route to access template applied image file
            $app['router']->get(
                config('imagecache.route').'/{template}/{filename}',
                'Intervention\Image\ImageCacheController@getImage'
            )->where(array('filename' => $filename_pattern));
        }
    }
}
