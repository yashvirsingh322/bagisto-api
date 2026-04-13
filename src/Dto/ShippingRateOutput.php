<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;

/**
 * ShippingRateOutput - GraphQL Output DTO for Shipping Rates
 *
 * Output for retrieving available shipping rates during checkout
 */
class ShippingRateOutput
{
    #[ApiProperty(identifier: true, readable: true, writable: false)]
    public ?string $id = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $code = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $label = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $price = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $formatted_price = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $description = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $method = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $method_title = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $method_description = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $base_price = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $base_formatted_price = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $carrier = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $carrier_title = null;
}
