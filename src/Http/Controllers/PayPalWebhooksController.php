<?php
declare(strict_types=1);

namespace Ankurk91\PayPalWebhooks\Http\Controllers;

use Ankurk91\PayPalWebhooks\PayPalWebhookConfig;
use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookProcessor;

class PayPalWebhooksController
{
    public function __invoke(Request $request)
    {
        $webhookConfig = PayPalWebhookConfig::get();

        return (new WebhookProcessor($request, $webhookConfig))->process();
    }
}
