<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Output DTO for customer profile mutation responses
 * Uses camelCase for GraphQL field names
 */
class CustomerProfileOutput
{
    #[ApiProperty(readable: true, writable: false)]
    #[SerializedName('id')]
    public ?string $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[SerializedName('firstName')]
    public ?string $firstName = null;

    #[ApiProperty(readable: true, writable: false)]
    #[SerializedName('lastName')]
    public ?string $lastName = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $email = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $phone = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $gender = null;

    #[ApiProperty(readable: true, writable: false)]
    #[SerializedName('dateOfBirth')]
    public ?string $dateOfBirth = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $status = null;

    #[ApiProperty(readable: true, writable: false)]
    #[SerializedName('subscribedToNewsLetter')]
    public ?bool $subscribedToNewsLetter = null;

    #[ApiProperty(readable: true, writable: false)]
    #[SerializedName('isVerified')]
    public ?string $isVerified = null;

    #[ApiProperty(readable: true, writable: false)]
    #[SerializedName('isSuspended')]
    public ?string $isSuspended = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $image = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?bool $success = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $message = null;

    public function __construct(
        ?string $id = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $gender = null,
        ?string $dateOfBirth = null,
        ?string $status = null,
        ?bool $subscribedToNewsLetter = null,
        ?string $isVerified = null,
        ?string $isSuspended = null,
        ?string $image = null,
        ?bool $success = null,
        ?string $message = null,
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->gender = $gender;
        $this->dateOfBirth = $dateOfBirth;
        $this->status = $status;
        $this->subscribedToNewsLetter = $subscribedToNewsLetter;
        $this->isVerified = $isVerified;
        $this->isSuspended = $isSuspended;
        $this->image = $image;
        $this->success = $success;
        $this->message = $message;
    }
}
