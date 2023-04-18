<?php
declare(strict_types=1);

namespace Ankurk91\PayPalWebhooks;

use Ankurk91\PayPalWebhooks\Exception\WebhookFailed;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;
use Throwable;

/**
 * @see https://stackoverflow.com/questions/61041128
 */
class PayPalSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        if (!config('paypal-webhooks.verify_signature')) {
            return true;
        }

        if (!$webhookID = config('paypal-webhooks.webhook_id')) {
            throw WebhookFailed::webhookIdIsNotSet();
        }

        try {
            if (!$this->hasValidCertDomain($request)) {
                return false;
            }

            return openssl_verify(
                    data: implode('|', [
                        $request->header('PAYPAL-TRANSMISSION-ID'),
                        $request->header('PAYPAL-TRANSMISSION-TIME'),
                        $webhookID,
                        crc32($request->getContent()),
                    ]),
                    signature: base64_decode($request->header('PAYPAL-TRANSMISSION-SIG')),
                    public_key: openssl_pkey_get_public($this->downloadCert($request->header('PAYPAL-CERT-URL'))),
                    algorithm: OPENSSL_ALGO_SHA256
                ) === 1;
        } catch (RequestException $e) {
            throw $e;
        } catch (Throwable $e) {
            report_if(app()->hasDebugModeEnabled(), $e);

            return false;
        }
    }

    protected function hasValidCertDomain(Request $request): bool
    {
        $url = (string) $request->header('PAYPAL-CERT-URL');
        $host = parse_url($url, PHP_URL_HOST) ?? '';

        return str_ends_with($host, 'paypal.com');
    }

    /**
     * @throws RequestException
     */
    protected function downloadCert(string $url): string
    {
        return Http::timeout(15)->get($url)->throw()->body();
    }
}
