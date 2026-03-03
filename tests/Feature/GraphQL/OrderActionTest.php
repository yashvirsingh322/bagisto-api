<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;
use Webkul\Product\Models\Product;
use Webkul\Checkout\Facades\Cart;

/**
 * Helper to create a test order for a customer.
 */
function createTestOrder($test, $customer, $status = 'pending') {
    $product = Product::factory()->create();
    
    $order = Order::factory()->create([
        'customer_id'         => $customer->id,
        'customer_email'      => $customer->email,
        'customer_first_name' => $customer->first_name,
        'customer_last_name'  => $customer->last_name,
        'status'              => $status,
    ]);

    OrderItem::factory()->create([
        'order_id'   => $order->id,
        'product_id' => $product->id,
        'qty_ordered' => 1,
    ]);

    OrderPayment::factory()->create(['order_id' => $order->id]);

    return $order;
}

class OrderActionTest extends GraphQLTestCase
{
    public function test_customer_can_cancel_their_own_pending_order(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $order = createTestOrder($this, $customer, 'pending');

        $mutation = <<<'GQL'
            mutation CancelOrder($input: CancelOrderInput!) {
                createCancelOrder(input: $input) {
                    cancelOrder {
                        success
                        message
                        status
                        orderId
                    }
                }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['orderId' => (int) $order->id]
        ]);

        $response->assertOk()
            ->assertJsonPath('data.createCancelOrder.cancelOrder.success', true)
            ->assertJsonPath('data.createCancelOrder.cancelOrder.status', 'canceled');
    }

    public function test_customer_cannot_cancel_an_order_that_does_not_belong_to_them(): void
    {
        $this->seedRequiredData();
        $customer1 = $this->createCustomer();
        $customer2 = $this->createCustomer();
        $orderOfCustomer2 = createTestOrder($this, $customer2);

        $mutation = <<<'GQL'
            mutation CancelOrder($input: CancelOrderInput!) {
                createCancelOrder(input: $input) {
                    cancelOrder { success }
                }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer1, $mutation, [
            'input' => ['orderId' => (int) $orderOfCustomer2->id]
        ]);

        // Should return a resource not found error
        expect($response->json('errors'))->not->toBeEmpty();
    }

    public function test_customer_can_reorder_items_from_a_previous_order(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $order = createTestOrder($this, $customer, 'completed');

        // Cart should be empty initially
        expect(Cart::getCart())->toBeNull();

        $mutation = <<<'GQL'
            mutation Reorder($input: ReorderInput!) {
                createReorderOrder(input: $input) {
                    reorderOrder {
                        success
                        itemsAddedCount
                    }
                }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['orderId' => (int) $order->id]
        ]);

        $response->assertOk()
            ->assertJsonPath('data.createReorderOrder.reorderOrder.success', true)
            ->assertJsonPath('data.createReorderOrder.reorderOrder.itemsAddedCount', 1);

        // Verify cart now has the item
        expect(Cart::getCart())->not->toBeNull();
        expect(Cart::getCart()->items->count())->toBe(1);
    }
}
