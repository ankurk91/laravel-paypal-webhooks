<?php
declare(strict_types=1);

namespace Ankurk91\PayPalWebhooks\Tests;

use Ankurk91\PayPalWebhooks\PayPalSignatureValidator;
use Ankurk91\PayPalWebhooks\PayPalWebhookConfig;
use Spatie\WebhookClient\WebhookConfig;

class PayPalSignatureValidatorTest extends TestCase
{
    private WebhookConfig $config;
    private PayPalSignatureValidator $validator;

    public function setUp(): void
    {
        parent::setUp();

        config()->set('paypal-webhooks.webhook_id', '6U272633NC098611R');

        $this->config = PayPalWebhookConfig::get();
        $this->validator = new PayPalSignatureValidator();
    }

    public function testValidSignature(): void
    {
        $this->markTestSkipped('Should we ask ChatGPT to write tests?.');
    }

}
