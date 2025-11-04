<?php

declare(strict_types=1);

namespace Shopware\AccountApi\Tests\Unit\Authentication;

use PHPUnit\Framework\TestCase;
use Shopware\AccountApi\Authentication\Token;

class TokenTest extends TestCase
{
    public function testFromApiResponse(): void
    {
        $data = [
            'token' => 'test-token-123',
            'expire' => [
                'date' => '2025-12-31 23:59:59',
            ],
            'userId' => 42,
        ];

        $token = Token::fromApiResponse($data);

        $this->assertSame('test-token-123', $token->token);
        $this->assertSame(42, $token->userAccountId);
        $this->assertInstanceOf(\DateTimeInterface::class, $token->expiresAt);
    }

    public function testIsValidWithFutureExpiry(): void
    {
        $futureDate = new \DateTimeImmutable('+1 day');
        $data = [
            'token' => 'test-token',
            'expire' => [
                'date' => $futureDate->format('Y-m-d H:i:s'),
            ],
            'userId' => 1,
        ];

        $token = Token::fromApiResponse($data);

        $this->assertTrue($token->isValid());
    }

    public function testIsValidWithPastExpiry(): void
    {
        $pastDate = new \DateTimeImmutable('-1 day');
        $data = [
            'token' => 'test-token',
            'expire' => [
                'date' => $pastDate->format('Y-m-d H:i:s'),
            ],
            'userId' => 1,
        ];

        $token = Token::fromApiResponse($data);

        $this->assertFalse($token->isValid());
    }

    public function testToArray(): void
    {
        $data = [
            'token' => 'test-token',
            'expire' => [
                'date' => '2025-12-31 23:59:59',
            ],
            'userId' => 1,
        ];

        $token = Token::fromApiResponse($data);
        $array = $token->toArray();

        $this->assertIsArray($array);
        $this->assertSame('test-token', $array['token']);
        $this->assertArrayHasKey('expire', $array);
        $this->assertSame(1, $array['userId']);
    }
}
