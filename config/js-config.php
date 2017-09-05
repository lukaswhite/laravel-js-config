<?php

return [
    /**
     * Path to the generated config file.
     */
    'output' => resource_path('assets/js/config.js'),

    /**
     * If true, output will be formatted with JSON_PRETTY_PRINT option.
     */
    'pretty' => true,

    /**
     * Config keys to publish.
     *
     * Each entry can be a specific key eg. 'app.env' or a group of keys like 'auth.defaults'.
     * Make sure you don't publish your application key or any passwords.
     */
    'keys' => [
        // 'app.env',
    ]
];
