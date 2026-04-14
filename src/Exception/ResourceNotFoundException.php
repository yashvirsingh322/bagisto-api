<?php

namespace Webkul\BagistoApi\Exception;

use GraphQL\Error\ClientAware;

/**
 * ResourceNotFoundException
 *
 * Thrown when a requested resource (cart, product, item, etc.) is not found.
 * This is for legitimate 404-type errors where the resource simply doesn't exist.
 *
 * Examples:
 * - Cart not found
 * - Product not found
 * - Cart item not found
 *
 * Status Code: 404 Not Found
 */
class ResourceNotFoundException extends \Exception implements ClientAware
{
    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'resource_not_found';
    }
}
