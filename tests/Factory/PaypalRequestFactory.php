<?php
declare(strict_types=1);

namespace Ankurk91\PayPalWebhooks\Tests\Factory;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaypalRequestFactory
{
    /**
     * The certificate to sign the request.
     */
    protected ?string $certificate;

    protected function getSignature(string $stringToSign): string
    {
        $privateKey = openssl_pkey_new();

        // Export certificate for later use
        $csr = openssl_csr_new([], $privateKey);
        $x509 = openssl_csr_sign($csr, null, $privateKey, 1);
        openssl_x509_export($x509, $this->certificate);

        openssl_sign($stringToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }

    public function getCertificate(): ?string
    {
        return $this->certificate;
    }

    public function make(string $webhookId): Request
    {
        $payload = PayPalWebhookFactory::checkoutOrderApproved();
        $request = Request::create('/webhooks/paypal', 'POST', [], [], [], [], json_encode($payload));

        $headers = [
            'PAYPAL-TRANSMISSION-ID' => Str::random(),
            'PAYPAL-TRANSMISSION-TIME' => now()->toDateTimeString(),
            'PAYPAL-CERT-URL' => 'https://sandbox.paypal.com/cert.pem',
        ];

        foreach ($headers as $name => $value) {
            $request->headers->set($name, $value);
        }

        $request->headers->set(
            'PAYPAL-TRANSMISSION-SIG',
            $this->getSignature($this->getStringToSign($request, $webhookId))
        );

        return $request;
    }

    protected function getStringToSign(Request $request, string $webhookID): string
    {
        return implode('|', [
            $request->header('PAYPAL-TRANSMISSION-ID'),
            $request->header('PAYPAL-TRANSMISSION-TIME'),
            $webhookID,
            crc32($request->getContent()),
        ]);
    }
}
