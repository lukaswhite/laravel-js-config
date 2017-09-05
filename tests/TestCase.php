<?php

namespace Tests;

use LaravelJsConfig\LaravelJsConfigServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelJsConfigServiceProvider::class
        ];
    }


}