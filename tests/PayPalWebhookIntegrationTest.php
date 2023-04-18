<?php
declare(strict_types=1);

namespace Ankurk91\PayPalWebhooks\Tests;

use Ankurk91\PayPalWebhooks\Exception\WebhookFailed;
use Ankurk91\PayPalWebhooks\Tests\Factory\PayPalWebhookFactory;
use Ankurk91\PayPalWebhooks\Tests\Fixtures\TestHandlerJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Spatie\WebhookClient\Models\WebhookCall;

class PayPalWebhookIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        config()->set('paypal-webhooks.verify_signature', false);
        config()->set('paypal-webhooks.jobs', [
            'checkout_order_approved' => TestHandlerJob::class,
            'checkout_order_completed' => 'UnknownJob::class',
        ]);
    }

    public function test_it_can_processes_webhook_successfully()
    {
        Event::fake();
        Bus::fake(TestHandlerJob::class);

        $payload = PayPalWebhookFactory::checkoutOrderApproved();

        $this->postJson('/webhooks/paypal', $payload)
            ->assertSuccessful();

        $this->assertDatabaseCount('webhook_calls', 1);

        Bus::assertDispatched(TestHandlerJob::class, function ($job) {
            $this->assertInstanceOf(WebhookCall::class, $job->webhookCall);

            return true;
        });

        Event::assertDispatched('paypal-webhooks::checkout_order_approved', function ($event, $eventPayload) {
            $this->assertInstanceOf(WebhookCall::class, $eventPayload);

            return true;
        });
    }

    public function test_it_fails_when_invalid_job_class_configured()
    {
        Event::fake();

        $payload = PayPalWebhookFactory::checkoutOrderCompleted();

        $this->postJson('/webhooks/paypal', $payload)
            ->assertSuccessful();

        Event::assertDispatched('paypal-webhooks::checkout_order_completed', function ($event, $eventPayload) {
            $this->assertInstanceOf(WebhookCall::class, $eventPayload);

            return true;
        });

        Event::assertDispatched(JobFailed::class, function ($event) {
            $this->assertInstanceOf(WebhookFailed::class, $event->exception);

            return true;
        });
    }

    public function test_it_process_same_webhook_only_once()
    {
        $payload = PayPalWebhookFactory::checkoutOrderApproved();

        $this->postJson('/webhooks/paypal', $payload)
            ->assertSuccessful();

        $this->postJson('/webhooks/paypal', $payload)
            ->assertSuccessful();

        $this->assertDatabaseCount('webhook_calls', 1);
    }
}
