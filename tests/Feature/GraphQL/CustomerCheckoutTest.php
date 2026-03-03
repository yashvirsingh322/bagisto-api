<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;

class CustomerCheckoutTest extends GraphQLTestCase
{
    /**
     * Helper method to get authorization headers with customer token
     */
    private function customerHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer ' . $token,
        ];
    }

    /**
     * Add product to cart first for checkout tests
     */
    private function addProductToCart(string $token): int
    {
        // Use test product helper to get a product with inventory
        $productData = $this->createTestProduct();
        $product = $productData['product'];

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $quantity: Int!) {
              createAddProductInCart(input: {productId: $productId, quantity: $quantity}) {
                addProductInCart {
                  id
                  itemsCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => $product->id,
            'quantity'  => 1,
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        return $product->id;
    }

    /**
     * Get Checkout Addresses (Customer)
     */
    public function test_get_checkout_addresses(): void
    {
        // First add product to cart to create a cart
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];
        
        // Add product to cart first to create a cart
        $this->addProductToCart($token);
        
        $query = <<<'GQL'
            query collectionGetCheckoutAddresses {
              collectionGetCheckoutAddresses {
                edges {
                  node {
                    id
                    _id
                    addressType
                    parentAddressId
                    firstName
                    lastName
                    gender
                    companyName
                    address
                    city
                    state
                    country
                    postcode
                    email
                    phone
                    vatId
                    defaultAddress
                    useForShipping
                    additional
                    createdAt
                    updatedAt
                    name
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, [], $this->customerHeaders($token));

        $response->assertSuccessful();

        $data = $response->json('data.collectionGetCheckoutAddresses');

        $this->assertNotNull($data, 'checkout addresses response is null');
        $this->assertIsArray($data['edges'] ?? [], 'edges is not an array');
    }

    /**
     * Get Shipping Methods (Customer)
     */
    public function test_get_shipping_methods(): void
    {
        // Use createTestCustomer to get customer token
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];
        
        // Add product to cart first
        $this->addProductToCart($token);
        
        $query = <<<'GQL'
            query checkoutShippingRates {
              collectionShippingRates {
                _id
                id
                code
                description
                method
                price
                label
              }
            }
        GQL;

        $response = $this->graphQL($query, [], $this->customerHeaders($token));

        $response->assertSuccessful();

        $data = $response->json('data.collectionShippingRates');

        $this->assertNotNull($data, 'shipping rates response is null');
        $this->assertIsArray($data, 'shipping rates is not an array');
    }

    /**
     * Get Payment Methods (Customer)
     */
    public function test_get_payment_methods(): void
    {
        // Use createTestCustomer to get customer token
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];
        
        // Add product to cart first
        $this->addProductToCart($token);
        
        $query = <<<'GQL'
            query checkoutPaymentMethods {
              collectionPaymentMethods {
                id
                _id
                method
                title
                description
                icon
                isAllowed
              }
            }
        GQL;

        $response = $this->graphQL($query, [], $this->customerHeaders($token));

        $response->assertSuccessful();

        $data = $response->json('data.collectionPaymentMethods');

        $this->assertNotNull($data, 'payment methods response is null');
        $this->assertIsArray($data, 'payment methods is not an array');
    }

    /**
     * Set Billing/Shipping Address (Customer)
     */
    public function test_set_checkout_address(): void
    {
        // Use createTestCustomer to get customer token
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];
        
        // First add product to cart
        $this->addProductToCart($token);
        
        $headers = $this->customerHeaders($token);

        $query = <<<'GQL'
            mutation createCheckoutAddress(
                $billingFirstName: String!
                $billingLastName: String!
                $billingEmail: String!
                $billingAddress: String!
                $billingCity: String!
                $billingCountry: String!
                $billingState: String!
                $billingPostcode: String!
                $billingPhoneNumber: String!
                $useForShipping: Boolean
            ) {
              createCheckoutAddress(
                input: {
                  billingFirstName: $billingFirstName
                  billingLastName: $billingLastName
                  billingEmail: $billingEmail
                  billingAddress: $billingAddress
                  billingCity: $billingCity
                  billingCountry: $billingCountry
                  billingState: $billingState
                  billingPostcode: $billingPostcode
                  billingPhoneNumber: $billingPhoneNumber
                  useForShipping: $useForShipping
                }
              ) {
                checkoutAddress {
                  _id
                  success
                  message
                  id
                  cartToken
                  billingFirstName
                  billingLastName
                  billingAddress
                  billingCity
                  billingState
                  billingPostcode
                  billingPhoneNumber
                  shippingFirstName
                  shippingLastName
                  shippingCity
                }
              }
            }
        GQL;

        $variables = [
            'billingFirstName' => 'John',
            'billingLastName' => 'Doe',
            'billingEmail' => 'john@example.com',
            'billingAddress' => '123 Main St',
            'billingCity' => 'Los Angeles',
            'billingCountry' => 'IN',
            'billingState' => 'UP',
            'billingPostcode' => '201301',
            'billingPhoneNumber' => '2125551234',
            'useForShipping' => true,
        ];

        $response = $this->graphQL($query, $variables, $headers);

        $response->assertSuccessful();

        $data = $response->json('data.createCheckoutAddress.checkoutAddress');

        $this->assertNotNull($data, 'checkout address response is null');
        $this->assertArrayHasKey('_id', $data);
        $this->assertArrayHasKey('success', $data);
    }

    /**
     * Set Shipping Method (Customer)
     */
    public function test_set_shipping_method(): void
    {
        // Use createTestCustomer to get customer token
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];
        
        // First add product to cart and set address
        $this->addProductToCart($token);
        $this->setCheckoutAddress($token);
        
        $headers = $this->customerHeaders($token);

        $query = <<<'GQL'
            mutation createCheckoutShippingMethod(
                $shippingMethod: String!
            ) {
              createCheckoutShippingMethod(
                input: {
                  shippingMethod: $shippingMethod
                }
              ) {
                checkoutShippingMethod {
                  success
                  id
                  message
                }
              }
            }
        GQL;

        $variables = [
            'shippingMethod' => 'flatrate_flatrate',
        ];

        $response = $this->graphQL($query, $variables, $headers);

        $response->assertSuccessful();

        $data = $response->json('data.createCheckoutShippingMethod.checkoutShippingMethod');

        $this->assertNotNull($data, 'checkout shipping method response is null');
        $this->assertArrayHasKey('success', $data);
    }

    /**
     * Set Payment Method (Customer)
     */
    public function test_set_payment_method(): void
    {
        // Use createTestCustomer to get customer token
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];
        
        // First add product to cart, set address, and set shipping
        $this->addProductToCart($token);
        $this->setCheckoutAddress($token);
        $this->setShippingMethod($token);
        
        $headers = $this->customerHeaders($token);

        $query = <<<'GQL'
            mutation createCheckoutPaymentMethod(
                $paymentMethod: String!,
                $successUrl: String,
                $failureUrl: String,
                $cancelUrl: String
            ) {
              createCheckoutPaymentMethod(
                input: {
                    paymentMethod: $paymentMethod,
                    paymentSuccessUrl: $successUrl,
                    paymentFailureUrl: $failureUrl,
                    paymentCancelUrl: $cancelUrl
                }
              ) {
                checkoutPaymentMethod {
                  success
                  message
                  paymentGatewayUrl
                  paymentData
                }
              }
            }
        GQL;

        $variables = [
            'paymentMethod' => 'moneytransfer',
            'successUrl' => 'https://myapp.com/payment/success',
            'failureUrl' => 'https://myapp.com/payment/failure',
            'cancelUrl' => 'https://myapp.com/payment/cancel',
        ];

        $response = $this->graphQL($query, $variables, $headers);

        $response->assertSuccessful();

        $data = $response->json('data.createCheckoutPaymentMethod.checkoutPaymentMethod');

        $this->assertNotNull($data, 'checkout payment method response is null');
        $this->assertArrayHasKey('success', $data);
    }

    /**
     * Place Order (Customer)
     */
    public function test_place_order(): void
    {
        // Use createTestCustomer to get customer token
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];
        
        // First add product to cart, set address, shipping and payment
        $this->addProductToCart($token);
        $this->setCheckoutAddress($token);
        $this->setShippingMethod($token);
        $this->setPaymentMethod($token);
        
        $headers = $this->customerHeaders($token);

        $query = <<<'GQL'
            mutation createCheckoutOrder {
              createCheckoutOrder(input:{}) {
                checkoutOrder {
                  id
                  orderId
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, [], $headers);

        $response->assertSuccessful();

        $data = $response->json('data.createCheckoutOrder.checkoutOrder');

        $this->assertNotNull($data, 'checkout order response is null');
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('orderId', $data);
    }

    /**
     * Helper to set checkout address
     */
    private function setCheckoutAddress(string $token): void
    {
        $headers = $this->customerHeaders($token);

        $query = <<<'GQL'
            mutation createCheckoutAddress(
                $billingFirstName: String!
                $billingLastName: String!
                $billingEmail: String!
                $billingAddress: String!
                $billingCity: String!
                $billingCountry: String!
                $billingState: String!
                $billingPostcode: String!
                $billingPhoneNumber: String!
                $useForShipping: Boolean
            ) {
              createCheckoutAddress(
                input: {
                  billingFirstName: $billingFirstName
                  billingLastName: $billingLastName
                  billingEmail: $billingEmail
                  billingAddress: $billingAddress
                  billingCity: $billingCity
                  billingCountry: $billingCountry
                  billingState: $billingState
                  billingPostcode: $billingPostcode
                  billingPhoneNumber: $billingPhoneNumber
                  useForShipping: $useForShipping
                }
              ) {
                checkoutAddress {
                  _id
                  success
                }
              }
            }
        GQL;

        $variables = [
            'billingFirstName' => 'John',
            'billingLastName' => 'Doe',
            'billingEmail' => 'john@example.com',
            'billingAddress' => '123 Main St',
            'billingCity' => 'Los Angeles',
            'billingCountry' => 'IN',
            'billingState' => 'UP',
            'billingPostcode' => '201301',
            'billingPhoneNumber' => '2125551234',
            'useForShipping' => true,
        ];

        $response = $this->graphQL($query, $variables, $headers);

        $response->assertSuccessful();
    }

    /**
     * Helper to set shipping method
     */
    private function setShippingMethod(string $token): void
    {
        $headers = $this->customerHeaders($token);

        $query = <<<'GQL'
            mutation createCheckoutShippingMethod(
                $shippingMethod: String!
            ) {
              createCheckoutShippingMethod(
                input: {
                  shippingMethod: $shippingMethod
                }
              ) {
                checkoutShippingMethod {
                  success
                  id
                }
              }
            }
        GQL;

        $variables = [
            'shippingMethod' => 'flatrate_flatrate',
        ];

        $response = $this->graphQL($query, $variables, $headers);

        $response->assertSuccessful();
    }

    /**
     * Helper to set payment method
     */
    private function setPaymentMethod(string $token): void
    {
        $headers = $this->customerHeaders($token);

        $query = <<<'GQL'
            mutation createCheckoutPaymentMethod(
                $paymentMethod: String!,
                $successUrl: String,
                $failureUrl: String,
                $cancelUrl: String
            ) {
              createCheckoutPaymentMethod(
                input: {
                    paymentMethod: $paymentMethod,
                    paymentSuccessUrl: $successUrl,
                    paymentFailureUrl: $failureUrl,
                    paymentCancelUrl: $cancelUrl
                }
              ) {
                checkoutPaymentMethod {
                  success
                }
              }
            }
        GQL;

        $variables = [
            'paymentMethod' => 'moneytransfer',
            'successUrl' => 'https://myapp.com/payment/success',
            'failureUrl' => 'https://myapp.com/payment/failure',
            'cancelUrl' => 'https://myapp.com/payment/cancel',
        ];

        $response = $this->graphQL($query, $variables, $headers);

        $response->assertSuccessful();
    }
}
