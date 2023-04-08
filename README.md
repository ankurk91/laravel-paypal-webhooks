# PayPal Webhooks Client for Laravel

[![Packagist](https://badgen.net/packagist/v/ankurk91/laravel-paypal-webhooks)](https://packagist.org/packages/ankurk91/laravel-paypal-webhooks)
[![GitHub-tag](https://badgen.net/github/tag/ankurk91/laravel-paypal-webhooks)](https://github.com/ankurk91/laravel-paypal-webhooks/tags)
[![License](https://badgen.net/packagist/license/ankurk91/laravel-paypal-webhooks)](LICENSE.txt)
[![Downloads](https://badgen.net/packagist/dt/ankurk91/laravel-paypal-webhooks)](https://packagist.org/packages/ankurk91/laravel-paypal-webhooks/stats)
[![GH-Actions](https://github.com/ankurk91/laravel-paypal-webhooks/workflows/tests/badge.svg)](https://github.com/ankurk91/laravel-paypal-webhooks/actions)
[![codecov](https://codecov.io/gh/ankurk91/laravel-paypal-webhooks/branch/main/graph/badge.svg)](https://codecov.io/gh/ankurk91/laravel-paypal-webhooks)

Handle [PayPal](https://developer.paypal.com/api/rest/webhooks/) webhooks in Laravel php framework.

## Installation

You can install the package via composer:

```bash
composer require "ankurk91/laravel-paypal-webhooks"
```

The service provider will automatically register itself.

You must publish the config file with:

```bash
php artisan vendor:publish --provider="Ankurk91\PayPalWebhooks\PayPalWebhooksServiceProvider"
```

Next, you must publish the migration with:

```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="webhook-client-migrations"
```

After the migration has been published you can create the `webhook_calls` table by running the migrations:

```bash
php artisan migrate
```

Next, for routing, add this route (guest) to your `routes/web.php`

```bash
Route::paypalWebhooks('/webhooks/paypal');
```

Behind the scenes this will register a `POST` route to a controller provided by this package. Next, you must add that
route to the `except` array of your `VerifyCsrfToken` middleware:

```php
protected $except = [
    '/webhooks/*',
];
```

It is recommended to set up a queue worker to precess the incoming webhooks.

## Setup PayPal account

* Login to PayPal developer [dashboard](https://developer.paypal.com/dashboard)
* Create a new Application (recommended)
* Create a new Webhook under the newly created application
* Enter your webhook URL. :bulb: You can use [ngrok](https://ngrok.com/) for local development
* Choose events to be tracked (Don't select all), for example:
    * Checkout order approved
* You will see a Webhook ID upon saving
* Specify this webhook ID in your `.env` like

```dotenv
PAYPAL_WEBHOOK_ID=6U272633NC098611R
```

This webhook ID will be used to verify the incoming request Signature.

### Troubleshoot

When using ngrok during development, you must update your `APP_URL` to match with ngrok vanity URL, for example:

```dotenv
APP_URL=https://af59-111-93-41-42.ngrok-free.app
```

You must verify that your webhook URL is publicly accessible by visiting the URL on terminal

```bash
curl -X POST https://af59-111-93-41-42.ngrok-free.app/webhooks/paypal
```

## Usage

There are 2 ways to handle incoming webhooks via this package.

### 1 - Handling webhook requests using jobs

If you want to do something when a specific event type comes in; you can define a job for that event.
Here's an example of such job:

```php
<?php

namespace App\Jobs\Webhook\PayPal;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\WebhookClient\Models\WebhookCall;

class CheckoutOrderApprovedJob implements ShouldQueue
{
    use SerializesModels;

    public function __construct(protected WebhookCall $webhookCall)
    {
        //
    }

    public function handle()
    {
        $message = $this->webhookCall->payload['resource'];
            
        // todo do something with $message['id']        
    }
}
```

After having created your job you must register it at the `jobs` array in the `config/paypal-webhooks.php` config file.
The key must be in lowercase and dots must be replaced by `_`.
The value must be a fully qualified classname.

```php
<?php

return [
     'jobs' => [
          'checkout_order_approved' => \App\Jobs\Webhook\PayPal\CheckoutOrderApprovedJob::clas,
     ],
];
```

### 2 - Handling webhook requests using events and listeners

Instead of queueing jobs to perform some work when a webhook request comes in, you can opt to listen to the events this
package will fire. Whenever a matching request hits your app, the package will fire
a `paypal-webhooks::<name-of-the-event>` event.

The payload of the events will be the instance of `WebhookCall` that was created for the incoming request.

You can listen for such event by registering the listener in your `EventServiceProvider` class.

```php
protected $listen = [
    'paypal-webhooks::payment_order_cancelled' => [
        App\Listeners\PayPal\PaymentOrderCancelledListener::class,
    ],
];
```

Here's an example of such listener class:

```php
<?php

namespace App\Listeners\PayPal;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\WebhookClient\Models\WebhookCall;

class PaymentOrderCancelledListener implements ShouldQueue
{
    public function handle(WebhookCall $webhookCall)
    {
        $message = $webhookCall->payload['resource'];
               
        // todo do something with $message        
    }
}
```

## Pruning old webhooks (opt-in but recommended)

Update your `app/Console/Kernel.php` file like:

```php
use Illuminate\Database\Console\PruneCommand;
use Spatie\WebhookClient\Models\WebhookCall;

$schedule->command(PruneCommand::class, [
            '--model' => [WebhookCall::class]
        ])
        ->onOneServer()
        ->daily()
        ->description('Prune webhook_calls.');
```

This will delete records older than `30` days, you can modify this duration by publishing this config file.

```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="webhook-client-config"
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

### Testing

```bash
composer test
```

### Security

If you discover any security issue, please email `pro.ankurk1[at]gmail[dot]com` instead of using the issue tracker.

### Useful Links

* https://developer.paypal.com/api/rest/webhooks/

### Acknowledgment

This package is highly inspired by:

* https://github.com/spatie/laravel-stripe-webhooks

### License

This package is licensed under [MIT License](https://opensource.org/licenses/MIT).
