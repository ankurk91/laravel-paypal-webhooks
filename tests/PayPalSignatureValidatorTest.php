<?php
declare(strict_types=1);

namespace Ankurk91\PayPalWebhooks\Tests;

use Ankurk91\PayPalWebhooks\PayPalSignatureValidator;
use Ankurk91\PayPalWebhooks\PayPalWebhookConfig;
use Ankurk91\PayPalWebhooks\Tests\Factory\PaypalRequestFactory;
use Illuminate\Support\Facades\Http;
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

    public function test_passes_for_valid_signature(): void
    {
        $factory = new PaypalRequestFactory();

        $request = $factory->make($this->webhookID);

        Http::fake([
            'paypal.com/*' => Http::response($factory->getCertificate())
        ]);

        $this->assertTrue($this->validator->isValid($request, $this->config));
    }

    public function test_failed_on_invalid_signature_url()
    {
        $factory = new PaypalRequestFactory();

        $request = $factory->make($this->webhookID);
        $request->headers->set('PAYPAL-CERT-URL', 'https://example.com/cert.pem');

        $this->assertFalse($this->validator->isValid($request, $this->config));
    }

}
