<?php
declare(strict_types=1);

namespace Ankurk91\PayPalWebhooks\Jobs;

use Ankurk91\PayPalWebhooks\Exception\WebhookFailed;
use Illuminate\Support\Str;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class ProcessPayPalWebhookJob extends ProcessWebhookJob
{
    public function handle(): void
    {
        $message = $this->webhookCall->payload;

        $eventKey = $this->createEventKey($message['event_type']);

        event("paypal-webhooks::$eventKey", $this->webhookCall);

        $jobClass = config("paypal-webhooks.jobs.$eventKey");

        if (empty($jobClass)) {
            return;
        }

        if (!class_exists($jobClass)) {
            $this->fail(WebhookFailed::jobClassDoesNotExist($jobClass));
            return;
        }

        dispatch(new $jobClass($this->webhookCall));
    }

    protected function createEventKey(string $eventType): string
    {
        return Str::of($eventType)->lower()->replace('.', '_')->value();
    }
}
