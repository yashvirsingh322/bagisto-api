<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\CartInput;
use Webkul\BagistoApi\State\CartTokenMutationProvider;
use Webkul\BagistoApi\State\CartTokenProcessor;

/**
 * AddProductInCart - GraphQL & REST API Resource for Adding Products to Cart
 *
 * Provides mutation for adding products to an existing shopping cart.
 * Uses token-based authentication for guest users or bearer token for authenticated users.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'AddProductInCart',
    uriTemplate: '/add-product-in-cart',
    operations: [
        new Post(
            name: 'addProduct',
            uriTemplate: '/add-product-in-cart',
            input: CartInput::class,
            output: CartData::class,
            provider: CartTokenMutationProvider::class,
            processor: CartTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Add product to cart. Can be used for both authenticated users and guests.',
            openapi: new Model\Operation(
                summary: 'Add product to cart',
                description: 'Add a product to the shopping cart with quantity and optional product options.',
                requestBody: new Model\RequestBody(
                    description: 'Product to add to cart',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'productId' => [
                                        'type' => 'integer',
                                        'example' => 1,
                                        'description' => 'Product ID',
                                    ],
                                    'quantity' => [
                                        'type' => 'integer',
                                        'example' => 1,
                                        'description' => 'Quantity',
                                    ],
                                    'options' => [
                                        'type' => 'object',
                                        'example' => ['size' => 'M', 'color' => 'blue'],
                                        'description' => 'Product options (optional)',
                                    ],
                                    'bundleOptions' => [
                                        'type' => 'string',
                                        'example' => '{"1":[1],"2":[2],"3":[3],"4":[4]}',
                                        'description' => 'Bundle options JSON (optional)',
                                    ],
                                    'bundleOptionQty' => [
                                        'type' => 'string',
                                        'example' => '{"1":1,"2":2,"3":1,"4":2}',
                                        'description' => 'Bundle option quantities JSON (optional)',
                                    ],
                                    'groupedQty' => [
                                        'type' => 'string',
                                        'example' => '{"101":2,"102":1}',
                                        'description' => 'Grouped product associated quantities JSON (optional, required for grouped products)',
                                    ],
                                    'booking' => [
                                        'type' => 'string',
                                        'example' => '{"type":"appointment","date":"2026-03-12","slot":"10:00 AM - 11:00 AM"}',
                                        'description' => 'Booking options JSON string (optional, required for booking products)',
                                    ],
                                    'specialNote' => [
                                        'type' => 'string',
                                        'example' => 'This is a special note',
                                        'description' => 'Special request / note (optional; merged into booking.note)',
                                    ],
                                ],
                            ],
                            'examples' => [
                                'simple_product' => [
                                    'summary' => 'Add Simple Product',
                                    'description' => 'Add a simple product to cart',
                                    'value' => [
                                        'productId' => 1,
                                        'quantity' => 1,
                                    ],
                                ],
                                'product_with_options' => [
                                    'summary' => 'Add Product with Options',
                                    'description' => 'Add a product with size and color options',
                                    'value' => [
                                        'productId' => 2,
                                        'quantity' => 2,
                                        'options' => ['size' => 'M', 'color' => 'blue'],
                                    ],
                                ],
                                'bundle_product' => [
                                    'summary' => 'Add Bundle Product',
                                    'description' => 'Add a bundle product with selected bundle options',
                                    'value' => [
                                        'productId' => 6,
                                        'quantity' => 1,
                                        'bundleOptions' => '{"1":[1],"2":[2],"3":[3],"4":[4]}',
                                        'bundleOptionQty' => '{"1":1,"2":2,"3":1,"4":2}',
                                    ],
                                ],
                                'grouped_product' => [
                                    'summary' => 'Add Grouped Product',
                                    'description' => 'Add a grouped product by specifying quantities for associated simple products',
                                    'value' => [
                                        'productId' => 5,
                                        'quantity' => 1,
                                        'groupedQty' => '{"101":2,"102":1}',
                                    ],
                                ],
                                'booking_product' => [
                                    'summary' => 'Add Booking Product',
                                    'description' => 'Add a booking product (appointment/default/table/rental/event) to cart by passing booking options as JSON string',
                                    'value' => [
                                        'productId' => 2555,
                                        'quantity' => 1,
                                        'booking' => '{"type":"appointment","date":"2026-03-12","slot":"10:00 AM - 11:00 AM"}',
                                    ],
                                ],
                                'event_booking_product' => [
                                    'summary' => 'Add Event Booking',
                                    'description' => 'Add an event booking product by selecting one or more ticket quantities (at least one ticket qty > 0 required)',
                                    'value' => [
                                        'productId' => 2564,
                                        'quantity' => 1,
                                        'booking' => '{"type":"event","qty":{"501":1,"502":2}}',
                                    ],
                                ],
                                'table_booking_product_with_note' => [
                                    'summary' => 'Add Table Booking (with note)',
                                    'description' => 'Add a table booking product and send special request/note separately',
                                    'value' => [
                                        'productId' => 2563,
                                        'quantity' => 1,
                                        'booking' => '{"type":"table","date":"2026-03-25","slot":"12:00 PM - 12:45 PM"}',
                                        'specialNote' => 'This is a special note',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CartInput::class,
            output: CartData::class,
            provider: CartTokenMutationProvider::class,
            processor: CartTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Add product to cart. Can be used for both authenticated users and guests.',
        ),
    ]
)]
class AddProductInCart
{
    #[ApiProperty(readable: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $cartToken = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?int $customerId = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?int $channelId = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?int $itemsCount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?array $items = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $subtotal = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $baseSubtotal = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $discountAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $baseDiscountAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $taxAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $baseTaxAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $shippingAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $baseShippingAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $grandTotal = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $baseGrandTotal = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $formattedSubtotal = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $formattedDiscountAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $formattedTaxAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $formattedShippingAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $formattedGrandTotal = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $couponCode = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?bool $success = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $message = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?array $carts = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $sessionToken = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?bool $isGuest = null;
}
