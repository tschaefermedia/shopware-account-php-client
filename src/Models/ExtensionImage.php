<?php

declare(strict_types=1);

namespace TschaeferMedia\ShopwareAccountApi\Models;

class ExtensionImage
{
    public function __construct(
        public readonly int $id,
        public readonly string $remoteLink,
        public array $details,
        public int $priority,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            remoteLink: $data['remoteLink'] ?? '',
            details: $data['details'] ?? [],
            priority: $data['priority'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'remoteLink' => $this->remoteLink,
            'details' => $this->details,
            'priority' => $this->priority,
        ];
    }
}
