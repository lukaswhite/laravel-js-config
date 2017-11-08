### Laravel-JS-Config

Easily publish your Laravel configuration to JavaScript.

#### Requirements

- PHP 7.*
- Laravel >= 5.2

#### Installation

```
composer require kkszymanowski/laravel-js-config
```

On Laravel < 5.5 add `LaravelJsConfig\LaravelJsConfigServiceProvider::class` to `app/config.php`.

On Laravel >= 5.5 the service provider should be automatically discovered. 

#### Publish assets

```
php artisan vendor:publish
```

#### Configure

In `config/js-config.php` there are following configuration options.

##### Output
Path of the output file generated from the command. By default `resources/assets/js/config.js`

##### Pretty
If true, the command will format the JSON configuration using `JSON_PRETTY_PRINT`.

##### Keys
List of configuration keys to be published.
Can be either a specific key(like `app.env`) or a group of keys(like `auth.defaults`)

**Make sure you don't publish your application key or any passwords.**

#### Run

```
php artisan config:js
```

#### Integration with Laravel Mix

To publish the configuration every time the Mix build starts follow these steps:
1. Install `webpack-shell-plugin`: 
    ```
    # NPM
    npm install webpack-shell-plugin --save-dev
    
    # Yarn
    yarn add webpack-shell-plugin 
    ```
2. In your `webpack.mix.js` file require the plugin
    ``` 
    const WebpackShellPlugin = require('webpack-shell-plugin');
    ```
    and register it to run before each build
    ``` 
    mix.webpackConfig({
        plugins: [
            new WebpackShellPlugin({
                onBuildStart: ['php artisan config:js'],
            }),
        ],
    });
    ```
    
If you're using the `watch` option in Laravel Mix and you want to refresh the published configuration you can either restart the build or run `php artisan config:js` in another terminal. 
