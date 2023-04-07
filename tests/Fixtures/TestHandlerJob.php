<?php
declare(strict_types=1);

namespace Ankurk91\PayPalWebhooks\Tests\Fixtures;

use Spatie\WebhookClient\Models\WebhookCall;

class TestHandlerJob
{
    public function __construct(public WebhookCall $webhookCall)
    {
        //
    }

    public function handle()
    {
        //
    }
}
