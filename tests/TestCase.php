<?php

namespace Talvbansal\TwillGlideDoSpaces\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Talvbansal\TwillGlideDoSpaces\TwillGlideDoSpacesServiceProvider;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            TwillGlideDoSpacesServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
