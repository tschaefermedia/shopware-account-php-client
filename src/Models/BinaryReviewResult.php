<?php

declare(strict_types=1);

namespace Shopware\AccountApi\Models;

class BinaryReviewResult
{
    public function __construct(
        public readonly int $id,
        public readonly int $binaryId,
        public readonly array $type,
        public readonly string $message,
        public readonly string $creationDate,
        public readonly array $subCheckResults,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            binaryId: $data['binaryId'] ?? 0,
            type: $data['type'] ?? [],
            message: $data['message'] ?? '',
            creationDate: $data['creationDate'] ?? '',
            subCheckResults: $data['subCheckResults'] ?? [],
        );
    }

    public function hasPassed(): bool
    {
        return $this->type['id'] === 3
            || strtolower($this->type['name'] ?? '') === 'automaticcodereviewsucceeded';
    }

    public function hasWarnings(): bool
    {
        foreach ($this->subCheckResults as $result) {
            if ($result['hasWarnings'] ?? false) {
                return true;
            }
        }

        return false;
    }

    public function isPending(): bool
    {
        return $this->type['id'] === 4;
    }

    public function getSummary(): string
    {
        $summary = [];
        foreach ($this->subCheckResults as $result) {
            if (!($result['passed'] ?? false)) {
                $summary[] = sprintf(
                    '%s: %s',
                    $result['subCheck'] ?? 'Unknown',
                    strip_tags($result['message'] ?? 'No message')
                );
            }
        }

        return implode("\n", $summary);
    }
}
