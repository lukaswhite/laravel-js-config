<?php

namespace LaravelJsConfig\Tests;

class DefaultConfigurationTest extends TestCase
{
    public function test_default_js_publish_configuration()
    {
        $this->assertEquals(resource_path('assets/js/config.js'), $this->app['config']['js-config.output']);
        $this->assertEquals(true, $this->app['config']['js-config.pretty']);
        $this->assertEquals([], $this->app['config']['js-config.keys']);
    }
}