<?php

declare(strict_types=1);

namespace Shopware\AccountApi\Authentication;

use DateTimeImmutable;

class Token
{
    public function __construct(
        public readonly string $token,
        public readonly DateTimeImmutable $expire,
        public readonly int $userAccountId,
        public readonly int $userId,
        public readonly bool $legacyLogin = false,
    ) {
    }

    public static function fromApiResponse(array $data): self
    {
        $expire = isset($data['expire']['date'])
            ? new DateTimeImmutable($data['expire']['date'])
            : new DateTimeImmutable('+1 hour');

        return new self(
            token: $data['token'],
            expire: $expire,
            userAccountId: $data['userAccountId'] ?? 0,
            userId: $data['userId'] ?? 0,
            legacyLogin: $data['legacyLogin'] ?? false,
        );
    }

    public function isValid(): bool
    {
        // Token is valid if it expires in more than 60 seconds
        $now = new DateTimeImmutable();

        return $this->expire->getTimestamp() - $now->getTimestamp() > 60;
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'expire' => [
                'date' => $this->expire->format('Y-m-d H:i:s.u'),
                'timezone_type' => 3,
                'timezone' => $this->expire->getTimezone()->getName(),
            ],
            'userAccountId' => $this->userAccountId,
            'userId' => $this->userId,
            'legacyLogin' => $this->legacyLogin,
        ];
    }
}
