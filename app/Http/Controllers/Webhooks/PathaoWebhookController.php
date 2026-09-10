<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\WebSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Pathao calling us when a parcel moves.
 *
 * Pathao offers no way to ask "what changed since last time", so without this
 * a status only moves when somebody presses Sync on the order page. The route
 * lives outside the web group deliberately: it must answer without a session,
 * without a CSRF token, and while the shop is in maintenance mode.
 */
class PathaoWebhookController extends Controller
{
    /**
     * The value Pathao looks for on our response before it will accept the
     * integration. It is a fixed constant published in Pathao's own plugin --
     * NOT the merchant's secret -- and identifies the answer as coming from a
     * real integration rather than any endpoint that happens to return 202.
     */
    private const INTEGRATION_SECRET = 'f3992ecc-59da-4cbe-a049-a13da2018d51';

    /**
     * Pathao names its events; the shipments table stores courier statuses.
     * Taken verbatim from Pathao's WooCommerce plugin so the statuses that
     * arrive by webhook read exactly like the ones a booking or a sync writes.
     */
    private const EVENT_STATUSES = [
        'order.created' => 'Order_Created',
        'order.updated' => 'Order_Updated',
        'order.pickup-requested' => 'Pickup_Requested',
        'order.assigned-for-pickup' => 'Assigned_for_Pickup',
        'order.picked' => 'Picked',
        'order.pickup-failed' => 'Pickup_Failed',
        'order.pickup-cancelled' => 'Pickup_Cancelled',
        'order.at-the-sorting-hub' => 'At_the_Sorting_HUB',
        'order.in-transit' => 'In_Transit',
        'order.received-at-last-mile-hub' => 'Received_at_Last_Mile_HUB',
        'order.assigned-for-delivery' => 'Assigned_for_Delivery',
        'order.delivered' => 'Delivered',
        'order.partial-delivery' => 'Partial_Delivery',
        'order.returned' => 'Return',
        'order.delivery-failed' => 'Delivery_Failed',
        'order.on-hold' => 'On_Hold',
        'order.paid-return' => 'paid_return',
        'order.exchanged' => 'exchange',
        'order.paid' => 'Payment_Invoice',
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $secret = WebSetting::first()?->pathao_webhook_secret;

        // No secret configured means nothing can be trusted yet, so nothing is
        // accepted -- an open endpoint would let anyone mark parcels delivered.
        if (blank($secret) || ! hash_equals($secret, (string) $request->header('X-PATHAO-Signature'))) {
            return response()->json(['message' => 'Invalid webhook signature.'], 401);
        }

        $event = (string) $request->input('event');

        // Pathao pings this once, when the webhook is saved in its panel.
        if ($event === 'webhook_integration') {
            return $this->accepted('Webhook integration verified.');
        }

        $status = self::EVENT_STATUSES[$event] ?? null;

        if ($status === null) {
            // A new event type is Pathao's business, not a failure of ours --
            // answering anything but 2xx would have them retry it forever.
            Log::info('Unrecognised Pathao webhook event.', ['event' => $event]);

            return $this->accepted('Event ignored.');
        }

        $shipment = $this->locate($request);

        if (! $shipment) {
            Log::warning('Pathao webhook for an unknown consignment.', [
                'consignment_id' => $request->input('consignment_id'),
                'merchant_order_id' => $request->input('merchant_order_id'),
            ]);

            return $this->accepted('No matching shipment.');
        }

        $shipment->update([
            'status' => $status,
            // Merged, so the delivery fee quoted at booking is not lost the
            // first time a status arrives without one.
            'raw_response' => array_merge($shipment->raw_response ?? [], $request->all()),
        ]);

        return $this->accepted('Status recorded.');
    }

    /**
     * Which shipment this call is about. The consignment id is the courier's
     * own handle and settles it outright; the order number is a fallback for
     * the events Pathao sends before one exists.
     */
    private function locate(Request $request): ?Shipment
    {
        if ($consignmentId = $request->input('consignment_id')) {
            $shipment = Shipment::where('consignment_id', $consignmentId)->first();

            if ($shipment) {
                return $shipment;
            }
        }

        if (! $orderNumber = $request->input('merchant_order_id')) {
            return null;
        }

        return Order::where('order_number', $orderNumber)
            ->first()?->shipments()
            ->where('courier', 'pathao')
            ->latest('id')
            ->first();
    }

    private function accepted(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 202)
            ->header('X-Pathao-Merchant-Webhook-Integration-Secret', self::INTEGRATION_SECRET);
    }
}
