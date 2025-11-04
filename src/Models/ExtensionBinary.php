<?php

declare(strict_types=1);

namespace Shopware\AccountApi\Models;

class ExtensionBinary
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $version,
        public readonly array $status,
        public readonly array $compatibleSoftwareVersions,
        public readonly array $changelogs,
        public readonly string $creationDate,
        public readonly string $lastChangeDate,
        public readonly bool $ionCubeEncrypted,
        public readonly bool $licenseCheckRequired,
        public readonly bool $hasActiveCodeReviewWarnings,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'] ?? '',
            version: $data['version'] ?? '',
            status: $data['status'] ?? [],
            compatibleSoftwareVersions: $data['compatibleSoftwareVersions'] ?? [],
            changelogs: $data['changelogs'] ?? [],
            creationDate: $data['creationDate'] ?? '',
            lastChangeDate: $data['lastChangeDate'] ?? '',
            ionCubeEncrypted: $data['ionCubeEncrypted'] ?? false,
            licenseCheckRequired: $data['licenseCheckRequired'] ?? false,
            hasActiveCodeReviewWarnings: $data['hasActiveCodeReviewWarnings'] ?? false,
        );
    }
}
