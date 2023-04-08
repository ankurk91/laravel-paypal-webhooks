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
    private string $webhookID = '6U272633NC098611R';

    public function setUp(): void
    {
        parent::setUp();

        config()->set('paypal-webhooks.webhook_id', $this->webhookID);

        $this->config = PayPalWebhookConfig::get();
        $this->validator = new PayPalSignatureValidator();
    }

    public function testValidSignature(): void
    {
        $this->markTestSkipped('Should we ask ChatGPT to write tests?.');
    }

}
