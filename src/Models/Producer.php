<?php

declare(strict_types=1);

namespace TschaeferMedia\ShopwareAccountApi\Models;

class Producer
{
    public function __construct(
        public readonly int $id,
        public readonly string $prefix,
        public readonly string $name,
        public readonly string $website,
        public readonly string $iconPath,
        public readonly bool $iconIsSet,
        public readonly string $shopwareId,
        public readonly int $userId,
        public readonly int $companyId,
        public readonly string $companyName,
        public readonly string $saleMail,
        public readonly string $supportMail,
        public readonly string $ratingMail,
        public readonly array $supportedLanguages,
        public readonly string $iconUrl,
        public readonly bool $hasSupportInfoActivated,
        public readonly bool $isPremiumExtensionPartner,
        public readonly array $details = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            prefix: $data['prefix'] ?? '',
            name: $data['name'] ?? '',
            website: $data['website'] ?? '',
            iconPath: $data['iconPath'] ?? '',
            iconIsSet: $data['iconIsSet'] ?? false,
            shopwareId: $data['shopwareID'] ?? '',
            userId: $data['userId'] ?? 0,
            companyId: $data['companyId'] ?? 0,
            companyName: $data['companyName'] ?? '',
            saleMail: $data['saleMail'] ?? '',
            supportMail: $data['supportMail'] ?? '',
            ratingMail: $data['ratingMail'] ?? '',
            supportedLanguages: $data['supportedLanguages'] ?? [],
            iconUrl: $data['iconURL'] ?? '',
            hasSupportInfoActivated: $data['hasSupportInfoActivated'] ?? false,
            isPremiumExtensionPartner: $data['isPremiumExtensionPartner'] ?? false,
            details: $data['details'] ?? [],
        );
    }
}
