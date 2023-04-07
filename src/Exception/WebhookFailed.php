<?php
declare(strict_types=1);

namespace Ankurk91\PayPalWebhooks\Exception;

use Exception;
use JsonException;

class WebhookFailed extends Exception
{
    public static function jobClassDoesNotExist(string $jobClass): static
    {
        return new static("Could not process webhook, the configured class `$jobClass` not found.");
    }

    public static function invalidJsonPayload(JsonException $exception): static
    {
        return new static('Failed to decode JSON payload', 500, $exception);
    }

    public static function webhookIdIsNotSet(): static
    {
        return new static('Signature verification failed since webhook_id is not set in config.');
    }
}
