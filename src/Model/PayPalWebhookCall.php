<?php
declare(strict_types=1);

namespace Ankurk91\PayPalWebhooks\Model;

use Ankurk91\PayPalWebhooks\Exception\WebhookFailed;
use Illuminate\Http\Request;
use JsonException;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfig;

class PayPalWebhookCall extends WebhookCall
{
    protected $table = 'webhook_calls';

    public static function storeWebhook(WebhookConfig $config, Request $request): WebhookCall
    {
        $headers = self::headersToStore($config, $request);

        return self::create([
            'name' => $config->name,
            'exception' => null,
            'url' => $request->path(),
            'headers' => $headers,
            'payload' => self::makePayload($request),
        ]);
    }

    protected static function makePayload(Request $request): array
    {
        try {
            return json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw  WebhookFailed::invalidJsonPayload($e);
        }
    }
}
