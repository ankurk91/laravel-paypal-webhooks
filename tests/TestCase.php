<?php
declare(strict_types=1);

namespace Ankurk91\PayPalWebhooks\Tests;

use Ankurk91\PayPalWebhooks\PayPalWebhooksServiceProvider;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Route::paypalWebhooks('/webhooks/paypal');

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [
            PayPalWebhooksServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('app.debug', false);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase(): void
    {
        $migration = include __DIR__.'/../vendor/spatie/laravel-webhook-client/database/migrations/create_webhook_calls_table.php.stub';

        $migration->up();
    }

}
