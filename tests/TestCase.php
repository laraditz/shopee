<?php

namespace Laraditz\Shopee\Tests;

use Laraditz\Shopee\ShopeeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
        
        // Run package migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            ShopeeServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup test environment configuration
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Setup Shopee configuration
        $app['config']->set('shopee.partner_id', 'test_partner_id');
        $app['config']->set('shopee.partner_key', 'test_partner_key');
        $app['config']->set('shopee.shop_id', 12345678);
        $app['config']->set('shopee.sandbox.mode', true);
    }
}