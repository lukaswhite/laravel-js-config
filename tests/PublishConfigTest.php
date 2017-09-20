<?php

namespace LaravelJsConfig\Tests;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;
use InvalidArgumentException;
use LaravelJsConfig\LaravelJsConfigServiceProvider;
use LaravelJsConfig\PublishConfig;
use Mockery as m;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class PublishConfigTest extends TestCase
{

    /** @var  Filesystem */
    protected $filesystem;

    public function setUp()
    {
        parent::setUp();

        $this->filesystem = new Filesystem();
    }

    public function test_it_writes_an_output_file()
    {
        $config = $this->makeConfig([
            'output' => 'foo',
            'pretty' => false,
            'keys'   => [],
        ]);

        $command = new PublishConfig($config, $this->filesystem);
        $output = $this->runCommand($command);

        $this->assertContains('Configuration published successfully to foo', $output);
        $this->assertContains('Do not edit', $this->filesystem->get('foo'));
    }

    public function test_it_publishes_specific_key()
    {
        $config = $this->makeConfig([
            'output' => 'foo',
            'pretty' => false,
            'keys'   => ['app.env'],
        ]);

        $config->shouldReceive('get')->withArgs(function ($key) {
            return $key === 'app.env';
        })->andReturn('local');

        $this->runCommand(new PublishConfig($config, $this->filesystem));

        $this->assertContains('export default {"app":{"env":"local"}}', $this->filesystem->get('foo'));
    }

    public function test_it_warns_about_cached_configuration()
    {
        $config = $this->makeConfig([
            'output' => 'foo',
            'pretty' => false,
            'keys'   => [],
        ]);

        $output = $this->runCommand(new PublishConfig($config, $this->filesystem), $configurationIsCached = true);

        $this->assertContains('Configuration has been cached', $output);
    }

    public function test_it_notifies_of_missing_key()
    {
        $config = $this->makeConfig([
            'output' => 'foo',
            'pretty' => false,
            'keys'   => ['app.env'],
        ]);

        $config->shouldReceive('get')
               ->withArgs(function ($key) {
                   return $key === 'app.env';
               })
               ->andReturnUsing(function ($key, Closure $default) {
                   $default();
               });

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Key [app.env] not found');

        $this->runCommand(new PublishConfig($config, $this->filesystem));
    }

    /**
     * @param array $packageConfig
     *
     * @return m\MockInterface|RepositoryContract
     */
    protected function makeConfig($packageConfig)
    {
        $config = m::mock(RepositoryContract::class);

        foreach ($packageConfig as $key => $value) {
            $config->shouldReceive('get')
                   ->with(LaravelJsConfigServiceProvider::CONFIG_NAME . '.' . $key)
                   ->andReturn($value);
        }

        return $config;
    }

    protected function runCommand(Command $command, $configurationIsCached = false)
    {
        $container = m::mock(Container::class)->makePartial();
        $container->shouldReceive('configurationIsCached')->andReturn($configurationIsCached);
        $command->setLaravel($container);

        $input = new StringInput('');
        $output = new BufferedOutput();

        $command->run($input, $output);

        return $output->fetch();
    }
}

class Filesystem extends IlluminateFilesystem
{

    protected $files = [];

    public function put($path, $contents, $lock = false)
    {
        $this->files[$path] = $contents;
    }

    public function get($path, $lock = false)
    {
        if (isset($this->files[$path])) {
            return $this->files[$path];
        }

        throw new FileNotFoundException("File does not exist at path {$path}");
    }
}
