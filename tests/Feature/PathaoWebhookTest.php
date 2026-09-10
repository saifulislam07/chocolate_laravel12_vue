<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\WebSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Pathao calling us when a parcel moves.
 *
 * Pathao will not accept the integration unless the endpoint answers 202 and
 * echoes a fixed header it looks for, so those are pinned here rather than left
 * to be discovered against the live panel. The signature is a shared secret
 * compared outright -- there is no HMAC -- which makes an unset secret the one
 * case that must never be treated as "nothing to check".
 */
class PathaoWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/api/webhooks/pathao';
    private const SECRET = 'a-long-random-webhook-secret';
    private const INTEGRATION_SECRET = 'f3992ecc-59da-4cbe-a049-a13da2018d51';

    private function configureWebhook(?string $secret = self::SECRET): void
    {
        WebSetting::create(['pathao_webhook_secret' => $secret]);
    }

    private function send(array $payload, ?string $signature = self::SECRET)
    {
        return $this->withHeaders($signature === null ? [] : ['X-PATHAO-Signature' => $signature])
            ->postJson(self::URL, $payload);
    }

    private function bookedShipment(string $status = 'Pending'): Shipment
    {
        $order = Order::create([
            'order_number' => 'CHOC-TEST-1',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => 100,
            'shipping_cost' => 0,
            'tax' => 0,
            'total' => 100,
            'customer_phone' => '01916665832',
        ]);

        return Shipment::create([
            'order_id' => $order->id,
            'courier' => 'pathao',
            'consignment_id' => 'DT100926JD76H6',
            'tracking_code' => 'DT100926JD76H6',
            'status' => $status,
            'raw_response' => ['data' => ['delivery_fee' => 110]],
        ]);
    }

    public function test_the_integration_ping_is_accepted_with_the_header_pathao_looks_for(): void
    {
        $this->configureWebhook();

        $this->send(['event' => 'webhook_integration'])
            ->assertStatus(202)
            ->assertHeader('X-Pathao-Merchant-Webhook-Integration-Secret', self::INTEGRATION_SECRET);
    }

    public function test_a_wrong_signature_is_refused(): void
    {
        $this->configureWebhook();
        $shipment = $this->bookedShipment();

        $this->send(['event' => 'order.delivered', 'consignment_id' => 'DT100926JD76H6'], 'not-the-secret')
            ->assertStatus(401);

        $this->assertSame('Pending', $shipment->fresh()->status);
    }

    public function test_a_missing_signature_is_refused(): void
    {
        $this->configureWebhook();

        $this->send(['event' => 'order.delivered'], null)->assertStatus(401);
    }

    public function test_an_unconfigured_secret_refuses_everything(): void
    {
        $this->configureWebhook(null);

        // Nothing is trusted while no secret is set -- an open endpoint would
        // let anyone mark parcels delivered.
        $this->send(['event' => 'webhook_integration'], '')->assertStatus(401);
        $this->send(['event' => 'order.delivered'])->assertStatus(401);
    }

    public static function eventProvider(): array
    {
        return [
            'picked' => ['order.picked', 'Picked', true],
            'in transit' => ['order.in-transit', 'In_Transit', true],
            'delivered' => ['order.delivered', 'Delivered', true],
            'returned' => ['order.returned', 'Return', false],
            'failed' => ['order.delivery-failed', 'Delivery_Failed', false],
            'cancelled' => ['order.pickup-cancelled', 'Pickup_Cancelled', false],
            'on hold' => ['order.on-hold', 'On_Hold', true],
        ];
    }

    #[DataProvider('eventProvider')]
    public function test_an_event_is_recorded_as_its_courier_status(string $event, string $status, bool $stillLive): void
    {
        $this->configureWebhook();
        $shipment = $this->bookedShipment();

        $this->send(['event' => $event, 'consignment_id' => 'DT100926JD76H6'])
            ->assertStatus(202)
            ->assertHeader('X-Pathao-Merchant-Webhook-Integration-Secret', self::INTEGRATION_SECRET);

        $shipment->refresh();

        $this->assertSame($status, $shipment->status);
        $this->assertSame($stillLive, $shipment->isLive());
    }

    public function test_a_shipment_is_found_by_order_number_when_no_consignment_is_given(): void
    {
        $this->configureWebhook();
        $shipment = $this->bookedShipment();

        $this->send(['event' => 'order.picked', 'merchant_order_id' => 'CHOC-TEST-1'])
            ->assertStatus(202);

        $this->assertSame('Picked', $shipment->fresh()->status);
    }

    public function test_the_quoted_delivery_fee_survives_a_webhook(): void
    {
        $this->configureWebhook();
        $shipment = $this->bookedShipment();

        $this->send(['event' => 'order.in-transit', 'consignment_id' => 'DT100926JD76H6']);

        $this->assertSame(110.0, $shipment->fresh()->deliveryFee());
    }

    public function test_an_unknown_event_is_accepted_rather_than_retried_forever(): void
    {
        $this->configureWebhook();
        $shipment = $this->bookedShipment();

        $this->send(['event' => 'order.teleported', 'consignment_id' => 'DT100926JD76H6'])
            ->assertStatus(202);

        $this->assertSame('Pending', $shipment->fresh()->status);
    }

    public function test_an_unknown_consignment_is_accepted_rather_than_retried_forever(): void
    {
        $this->configureWebhook();

        $this->send(['event' => 'order.delivered', 'consignment_id' => 'DT-SOMEONE-ELSE'])
            ->assertStatus(202);
    }

    public function test_the_webhook_answers_while_the_shop_is_in_maintenance(): void
    {
        WebSetting::create([
            'pathao_webhook_secret' => self::SECRET,
            'maintenance_mode' => true,
        ]);

        $shipment = $this->bookedShipment();

        $this->send(['event' => 'order.delivered', 'consignment_id' => 'DT100926JD76H6'])
            ->assertStatus(202);

        $this->assertSame('Delivered', $shipment->fresh()->status);
    }

    public function test_the_webhook_needs_no_csrf_token(): void
    {
        $this->configureWebhook();
        $this->bookedShipment();

        // A plain form post, not postJson: had this route been registered in
        // web.php by mistake, CSRF would turn it into a 419 here.
        $this->withHeaders(['X-PATHAO-Signature' => self::SECRET])
            ->post(self::URL, ['event' => 'order.picked', 'consignment_id' => 'DT100926JD76H6'])
            ->assertStatus(202);
    }
}
