<?php
declare(strict_types=1);

namespace Ankurk91\PayPalWebhooks\Tests\Factory;

class PayPalWebhookFactory
{
    public static function checkoutOrderApproved(): array
    {
        return self::readFile('checkout_order_approved.json');
    }

    public static function checkoutOrderCompleted(): array
    {
        return self::readFile('checkout_order_completed.json');
    }

    protected static function readFile(string $name): array
    {
        $body = file_get_contents(__DIR__.'/Messages/'.$name);
        return json_decode($body, true);
    }
}
