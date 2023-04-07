<?php
declare(strict_types=1);

namespace Ankurk91\PayPalWebhooks;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PayPalWebhooksServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->getConfigPath() => config_path('paypal-webhooks.php'),
            ], 'config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'paypal-webhooks');

        Route::macro('paypalWebhooks', function (string $url) {
            return Route::post($url, '\Ankurk91\PayPalWebhooks\Http\Controllers\PayPalWebhooksController')
                ->name('paypalWebhooks');
        });
    }

    protected function getConfigPath(): string
    {
        return __DIR__ . '/../config/paypal-webhooks.php';
    }
}
