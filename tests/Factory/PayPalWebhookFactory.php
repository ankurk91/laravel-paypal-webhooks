<?php
declare(strict_types=1);

namespace Ankurk91\PayPalWebhooks\Tests\Factory;

class PayPalWebhookFactory
{
    public static function checkoutOrderApproved(): array
    {
        $body = file_get_contents(__DIR__.'/Messages/checkout_order_approved.json');
        return json_decode($body, true);
    }

    public static function checkoutOrderCompleted(): array
    {
        $body = file_get_contents(__DIR__.'/Messages/checkout_order_completed.json');
        return json_decode($body, true);
    }
}
