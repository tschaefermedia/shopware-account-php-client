<?php

declare(strict_types=1);

namespace Shopware\AccountApi\Tests\Unit\Validation;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shopware\AccountApi\Validation\Validator;

class ValidatorTest extends TestCase
{
    public function testNotEmptyWithValidString(): void
    {
        $this->expectNotToPerformAssertions();
        Validator::notEmpty('test', 'Field');
    }

    public function testNotEmptyWithEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field cannot be empty');
        Validator::notEmpty('', 'Field');
    }

    public function testNotEmptyWithWhitespace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Validator::notEmpty('   ', 'Field');
    }

    public function testPositiveWithValidInteger(): void
    {
        $this->expectNotToPerformAssertions();
        Validator::positive(5, 'Field');
    }

    public function testPositiveWithZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field must be positive');
        Validator::positive(0, 'Field');
    }

    public function testPositiveWithNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Validator::positive(-5, 'Field');
    }

    public function testNotEmptyArrayWithValidArray(): void
    {
        $this->expectNotToPerformAssertions();
        Validator::notEmptyArray(['item'], 'Field');
    }

    public function testNotEmptyArrayWithEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field cannot be empty');
        Validator::notEmptyArray([], 'Field');
    }

    public function testEmailWithValidEmail(): void
    {
        $this->expectNotToPerformAssertions();
        Validator::email('test@example.com', 'Email');
    }

    public function testEmailWithInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email must be a valid email address');
        Validator::email('invalid-email', 'Email');
    }

    public function testVersionWithValidVersion(): void
    {
        $this->expectNotToPerformAssertions();
        Validator::version('1.0.0', 'Version');
        Validator::version('2.5.3-beta', 'Version');
    }

    public function testVersionWithInvalidVersion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Version must be in semantic versioning format');
        Validator::version('invalid', 'Version');
    }

    public function testUrlWithValidUrl(): void
    {
        $this->expectNotToPerformAssertions();
        Validator::url('https://example.com', 'URL');
    }

    public function testUrlWithInvalidUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('URL must be a valid URL');
        Validator::url('not-a-url', 'URL');
    }

    public function testInWithValidValue(): void
    {
        $this->expectNotToPerformAssertions();
        Validator::in('option1', ['option1', 'option2'], 'Field');
    }

    public function testInWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field must be one of: option1, option2');
        Validator::in('option3', ['option1', 'option2'], 'Field');
    }

    public function testLengthWithValidString(): void
    {
        $this->expectNotToPerformAssertions();
        Validator::length('test', 1, 10, 'Field');
    }

    public function testLengthWithTooShortString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field must be between 5 and 10 characters');
        Validator::length('test', 5, 10, 'Field');
    }

    public function testLengthWithTooLongString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Validator::length('test string that is too long', 1, 5, 'Field');
    }
}
