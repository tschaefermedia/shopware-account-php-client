<?php

declare(strict_types=1);

namespace TschaeferMedia\ShopwareAccountApi\Models;

class Profile
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $creationDate,
        public readonly bool $banned,
        public readonly bool $verified,
        public readonly array $personalData,
        public readonly bool $partnerMarketingOptIn,
        public readonly ?array $selectedMembership,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            email: $data['email'],
            creationDate: $data['creationDate'],
            banned: $data['banned'] ?? false,
            verified: $data['verified'] ?? false,
            personalData: $data['personalData'] ?? [],
            partnerMarketingOptIn: $data['partnerMarketingOptIn'] ?? false,
            selectedMembership: $data['selectedMembership'] ?? null,
        );
    }
}
