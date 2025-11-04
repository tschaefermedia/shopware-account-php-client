<?php

declare(strict_types=1);

namespace TschaeferMedia\ShopwareAccountApi\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use TschaeferMedia\ShopwareAccountApi\Models\Membership;

class MembershipTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'id' => 123,
            'creationDate' => '2024-01-01 12:00:00',
            'active' => true,
            'member' => ['id' => 789],
            'company' => [
                'id' => 456,
                'name' => 'Test Company',
            ],
            'roles' => [
                ['name' => 'admin'],
                ['name' => 'developer'],
            ],
        ];

        $membership = Membership::fromArray($data);

        $this->assertSame(123, $membership->id);
        $this->assertTrue($membership->active);
        $this->assertIsArray($membership->company);
        $this->assertSame(456, $membership->company['id']);
    }

    public function testGetRoles(): void
    {
        $data = [
            'id' => 123,
            'creationDate' => '2024-01-01 12:00:00',
            'active' => true,
            'member' => ['id' => 789],
            'company' => ['id' => 456],
            'roles' => [
                ['name' => 'admin'],
                ['name' => 'developer'],
            ],
        ];

        $membership = Membership::fromArray($data);
        $roles = $membership->getRoles();

        $this->assertSame(['admin', 'developer'], $roles);
    }

    public function testToArray(): void
    {
        $data = [
            'id' => 123,
            'creationDate' => '2024-01-01 12:00:00',
            'active' => true,
            'member' => ['id' => 789],
            'company' => ['id' => 456],
            'roles' => [
                ['name' => 'admin'],
            ],
        ];

        $membership = Membership::fromArray($data);
        $array = $membership->toArray();

        $this->assertIsArray($array);
        $this->assertSame(123, $array['id']);
        $this->assertTrue($array['active']);
        $this->assertIsArray($array['company']);
    }
}
