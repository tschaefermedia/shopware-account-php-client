<?php

declare(strict_types=1);

namespace TschaeferMedia\ShopwareAccountApi\Models;

class Membership
{
    public function __construct(
        public readonly int $id,
        public readonly string $creationDate,
        public readonly bool $active,
        public readonly array $member,
        public readonly array $company,
        public readonly array $roles,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            creationDate: $data['creationDate'],
            active: $data['active'] ?? false,
            member: $data['member'] ?? [],
            company: $data['company'] ?? [],
            roles: $data['roles'] ?? [],
        );
    }

    /**
     * Get role names
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        return array_map(fn (array $role) => $role['name'], $this->roles);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'creationDate' => $this->creationDate,
            'active' => $this->active,
            'member' => $this->member,
            'company' => $this->company,
            'roles' => $this->roles,
        ];
    }
}
