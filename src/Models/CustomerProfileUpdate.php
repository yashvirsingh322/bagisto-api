<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\CustomerProfileInput;
use Webkul\BagistoApi\Dto\CustomerProfileOutput;
use Webkul\BagistoApi\State\CustomerProfileProcessor;

/**
 * Customer profile update resource
 * Handles authenticated customer profile updates
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerProfileUpdate',
    uriTemplate: '/customer-profile-updates',
    operations: [
        new Put(uriTemplate: '/customer-profile-updates/{id}'),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CustomerProfileInput::class,
            output: CustomerProfileOutput::class,
            processor: CustomerProfileProcessor::class,
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            description: 'Update authenticated customer profile (requires token and at least one field). Re-query readCustomerProfile for updated data.',
        ),
    ]
)]
class CustomerProfileUpdate
{
    #[ApiProperty(readable: true, writable: false, identifier: true)]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $firstName = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $lastName = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $email = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $phone = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $gender = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $dateOfBirth = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $password = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $confirmPassword = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $status = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?bool $subscribedToNewsLetter = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $isVerified = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $isSuspended = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $image = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?bool $deleteImage = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?bool $success = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $message = null;
}
