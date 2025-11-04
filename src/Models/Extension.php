<?php

declare(strict_types=1);

namespace TschaeferMedia\ShopwareAccountApi\Models;

class Extension
{
    public function __construct(
        public int $id,
        public array $producer,
        public array $type,
        public string $name,
        public string $code,
        public string $moduleKey,
        public array $lifecycleStatus,
        public array $generation,
        public array $activationStatus,
        public array $approvalStatus,
        public array $standardLocale,
        public array $license,
        public array $infos = [],
        public array $priceModels = [],
        public array $variants = [],
        public array $storeAvailabilities = [],
        public array $categories = [],
        public ?array $category = null,
        public array $addons = [],
        public string $lastChange = '',
        public string $creationDate = '',
        public bool $support = false,
        public bool $supportOnlyCommercial = false,
        public string $iconPath = '',
        public bool $iconIsSet = false,
        public string $examplePageUrl = '',
        public bool $migrationSupport = false,
        public bool $automaticBugfixVersionCompatibility = false,
        public bool $hiddenInStore = false,
        public bool $isPremiumPlugin = false,
        public bool $isAdvancedFeature = false,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            producer: $data['producer'] ?? [],
            type: $data['type'] ?? [],
            name: $data['name'] ?? '',
            code: $data['code'] ?? '',
            moduleKey: $data['moduleKey'] ?? '',
            lifecycleStatus: $data['lifecycleStatus'] ?? [],
            generation: $data['generation'] ?? [],
            activationStatus: $data['activationStatus'] ?? [],
            approvalStatus: $data['approvalStatus'] ?? [],
            standardLocale: $data['standardLocale'] ?? [],
            license: $data['license'] ?? [],
            infos: $data['infos'] ?? [],
            priceModels: $data['priceModels'] ?? [],
            variants: $data['variants'] ?? [],
            storeAvailabilities: $data['storeAvailabilities'] ?? [],
            categories: $data['categories'] ?? [],
            category: $data['category'] ?? null,
            addons: $data['addons'] ?? [],
            lastChange: $data['lastChange'] ?? '',
            creationDate: $data['creationDate'] ?? '',
            support: $data['support'] ?? false,
            supportOnlyCommercial: $data['supportOnlyCommercial'] ?? false,
            iconPath: $data['iconPath'] ?? '',
            iconIsSet: $data['iconIsSet'] ?? false,
            examplePageUrl: $data['examplePageUrl'] ?? '',
            migrationSupport: $data['migrationSupport'] ?? false,
            automaticBugfixVersionCompatibility: $data['automaticBugfixVersionCompatibility'] ?? false,
            hiddenInStore: $data['hiddenInStore'] ?? false,
            isPremiumPlugin: $data['isPremiumPlugin'] ?? false,
            isAdvancedFeature: $data['isAdvancedFeature'] ?? false,
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
