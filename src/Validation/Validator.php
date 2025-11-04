<?php

declare(strict_types=1);

namespace TschaeferMedia\ShopwareAccountApi\Validation;

use InvalidArgumentException;

class Validator
{
    /**
     * Validate that a string is not empty
     *
     * @throws InvalidArgumentException
     */
    public static function notEmpty(string $value, string $fieldName): void
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException("$fieldName cannot be empty");
        }
    }

    /**
     * Validate that a value is positive
     *
     * @throws InvalidArgumentException
     */
    public static function positive(int|float $value, string $fieldName): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("$fieldName must be positive");
        }
    }

    /**
     * Validate that an array is not empty
     *
     * @throws InvalidArgumentException
     */
    public static function notEmptyArray(array $value, string $fieldName): void
    {
        if (empty($value)) {
            throw new InvalidArgumentException("$fieldName cannot be empty");
        }
    }

    /**
     * Validate email format
     *
     * @throws InvalidArgumentException
     */
    public static function email(string $value, string $fieldName = 'Email'): void
    {
        self::notEmpty($value, $fieldName);

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("$fieldName must be a valid email address");
        }
    }

    /**
     * Validate version format (semantic versioning)
     *
     * @throws InvalidArgumentException
     */
    public static function version(string $value, string $fieldName = 'Version'): void
    {
        self::notEmpty($value, $fieldName);

        if (!preg_match('/^\d+\.\d+\.\d+/', $value)) {
            throw new InvalidArgumentException("$fieldName must be in semantic versioning format (e.g., 1.0.0)");
        }
    }

    /**
     * Validate file path exists
     *
     * @throws InvalidArgumentException
     */
    public static function fileExists(string $path, string $fieldName = 'File'): void
    {
        self::notEmpty($path, $fieldName);

        if (!file_exists($path)) {
            throw new InvalidArgumentException("$fieldName does not exist: $path");
        }
    }

    /**
     * Validate file is readable
     *
     * @throws InvalidArgumentException
     */
    public static function fileReadable(string $path, string $fieldName = 'File'): void
    {
        self::fileExists($path, $fieldName);

        if (!is_readable($path)) {
            throw new InvalidArgumentException("$fieldName is not readable: $path");
        }
    }

    /**
     * Validate URL format
     *
     * @throws InvalidArgumentException
     */
    public static function url(string $value, string $fieldName = 'URL'): void
    {
        self::notEmpty($value, $fieldName);

        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("$fieldName must be a valid URL");
        }
    }

    /**
     * Validate value is in allowed list
     *
     * @throws InvalidArgumentException
     */
    public static function in(mixed $value, array $allowed, string $fieldName): void
    {
        if (!in_array($value, $allowed, true)) {
            $allowedStr = implode(', ', $allowed);

            throw new InvalidArgumentException("$fieldName must be one of: $allowedStr");
        }
    }

    /**
     * Validate string length
     *
     * @throws InvalidArgumentException
     */
    public static function length(string $value, int $min, int $max, string $fieldName): void
    {
        $length = strlen($value);

        if ($length < $min || $length > $max) {
            throw new InvalidArgumentException("$fieldName must be between $min and $max characters");
        }
    }

    /**
     * Validate image dimensions
     *
     * @throws InvalidArgumentException
     */
    public static function imageDimensions(string $path, int $maxWidth, int $maxHeight, string $fieldName = 'Image'): void
    {
        self::fileReadable($path, $fieldName);

        $imageSize = @getimagesize($path);

        if ($imageSize === false) {
            throw new InvalidArgumentException("$fieldName is not a valid image file");
        }

        [$width, $height] = $imageSize;

        if ($width > $maxWidth || $height > $maxHeight) {
            throw new InvalidArgumentException(
                "$fieldName dimensions ({$width}x{$height}) exceed maximum allowed ({$maxWidth}x{$maxHeight})"
            );
        }
    }
}
