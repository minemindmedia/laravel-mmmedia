<?php

namespace Mmmedia\Media\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Mmmedia\Media\MediaServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            MediaServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__ . '/../database/migrations/2024_01_01_000001_create_media_items_table.php';
        $migration->up();

        $migration = include __DIR__ . '/../database/migrations/2024_01_01_000002_create_media_usages_table.php';
        $migration->up();
    }
}
