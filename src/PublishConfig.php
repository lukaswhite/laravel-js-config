<?php

namespace LaravelJsConfig;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;

class PublishConfig extends Command
{

    /** @var string */
    protected $signature = 'config:js';

    /** @var string */
    protected $description = 'Publish configuration to JavaScript';

    /** @var Repository */
    protected $config;

    /** @var Filesystem */
    protected $files;

    /** @var string */
    protected static $separator = '.';

    public function __construct(Repository $config, Filesystem $files)
    {
        parent::__construct();

        $this->config = $config;
        $this->files = $files;
    }

    public function handle()
    {
        if($this->laravel->configurationIsCached()) {
            $this->warn("\nConfiguration has been cached. I'll assume you know what you're doing.");
        }

        $path = $this->writeConfig($this->fetchConfig());

        $this->info("\nConfiguration published successfully to $path\n");
    }

    protected function fetchConfig() : array
    {
        return array_reduce($this->getKeys(), [$this, 'mergeConfig'], []);
    }

    protected function writeConfig(array $config)
    {
        $path = $this->config->get('js-config.output');

        $this->files->put($path, $this->configToJavascript($config));

        return $path;
    }

    protected function configToJavascript(array $config)
    {
        $json = json_encode($config, $this->config->get('js-config.pretty') ? JSON_PRETTY_PRINT : 0);

        return <<<EOT
// Do not edit. File generated automatically.
// Run "php artisan {$this->signature}" to refresh published configuration.
export default $json
EOT;
    }

    protected function getKeys() : array
    {
        return $this->config->get('js-config.keys');
    }

    protected function mergeConfig(array $config, string $key) : array
    {
        return array_replace_recursive(
            $config,
            $this->fetchConfigRecursive($this->splitKey($key))
        );
    }

    protected function fetchConfigRecursive(array $parts, array $previousParts = []) : array
    {
        $firstPart = $previousParts[] = array_shift($parts);

        return [
            $firstPart =>
                empty($parts)
                    ? $this->fetchSingleConfigEntry($this->joinKeyParts($previousParts))
                    : $this->fetchConfigRecursive($parts, $previousParts),
        ];
    }

    protected function fetchSingleConfigEntry(string $key)
    {
        return $this->config->get($key, function () use ($key) {
            throw new InvalidArgumentException("Key [$key] not found");
        });
    }

    protected function splitKey(string $key) : array
    {
        return explode(static::$separator, $key);
    }

    protected function joinKeyParts(array $keyParts) : string
    {
        return implode(static::$separator, $keyParts);
    }
}
