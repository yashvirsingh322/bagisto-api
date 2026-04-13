<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * DTO for creating a compare item
 *
 * Defines the input structure for the createCompareItem mutation
 * Customer ID is automatically determined from the authenticated user
 */
class CreateCompareItemInput
{
    /**
     * Product ID to add to comparison
     */
    #[ApiProperty(description: 'The ID of the product to add to comparison')]
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
