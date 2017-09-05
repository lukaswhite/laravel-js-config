<?php

namespace LaravelJsConfig;

use Illuminate\Support\ServiceProvider;

class LaravelJsConfigServiceProvider extends ServiceProvider
{

    const CONFIG_NAME = 'js-config';

    const CONFIG_PATH = __DIR__ . '/../config/' . self::CONFIG_NAME . '.php';

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishConfig::class,
            ]);
        }

        $this->publishes([
            self::CONFIG_PATH => $this->app['path.config'] . '/' . self::CONFIG_NAME . '.php',
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, self::CONFIG_NAME);
    }
}
