<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * DTO for creating a wishlist item
 *
 * Defines the input structure for the createWishlist mutation
 * Customer ID and channel ID are automatically determined from the authenticated user and current channel
 */
class CreateWishlistInput
{
    /**
     * Product ID to add to wishlist
     */
    #[ApiProperty(description: 'The ID of the product to add to wishlist')]
    #[Groups(['mutation'])]
    public ?int $product_id = null;

    public function getProduct_id(): ?int
    {
        return $this->product_id;
    }

    public function setProduct_id(?int $product_id): void
    {
        $this->product_id = $product_id;
    }
}
