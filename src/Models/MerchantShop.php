<?php

declare(strict_types=1);

namespace Shopware\AccountApi\Models;

class MerchantShop
{
    public function __construct(
        public readonly int $id,
        public readonly string $domain,
        public readonly string $type,
        public readonly int $companyId,
        public readonly string $companyName,
        public readonly int $dispo,
        public readonly float $balance,
        public readonly bool $isPartnerShop,
        public readonly ?int $subaccount,
        public readonly bool $isCommercial,
        public readonly string $documentComment,
        public readonly bool $activated,
        public readonly string $accountId,
        public readonly string $shopNumber,
        public readonly string $creationDate,
        public readonly array $subscriptionModules,
        public readonly array $environment,
        public readonly bool $staging,
        public readonly bool $instance,
        public readonly bool $mandant,
        public readonly array $shopwareVersion,
        public readonly string $shopwareEdition,
        public readonly string $domainIdn,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            domain: $data['domain'] ?? '',
            type: $data['type'] ?? '',
            companyId: $data['companyId'] ?? 0,
            companyName: $data['companyName'] ?? '',
            dispo: $data['dispo'] ?? 0,
            balance: $data['balance'] ?? 0.0,
            isPartnerShop: $data['isPartnerShop'] ?? false,
            subaccount: $data['subaccount'] ?? null,
            isCommercial: $data['isCommercial'] ?? false,
            documentComment: $data['documentComment'] ?? '',
            activated: $data['activated'] ?? false,
            accountId: $data['accountId'] ?? '',
            shopNumber: $data['shopNumber'] ?? '',
            creationDate: $data['creationDate'] ?? '',
            subscriptionModules: $data['subscriptionModules'] ?? [],
            environment: $data['environment'] ?? [],
            staging: $data['staging'] ?? false,
            instance: $data['instance'] ?? false,
            mandant: $data['mandant'] ?? false,
            shopwareVersion: $data['shopwareVersion'] ?? [],
            shopwareEdition: $data['shopwareEdition'] ?? '',
            domainIdn: $data['domainIdn'] ?? '',
        );
    }
}
