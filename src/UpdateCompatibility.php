<?php

declare(strict_types=1);

namespace Shopware\AccountApi;

use GuzzleHttp\Client as HttpClient;

class UpdateCompatibility
{
    private const BASE_URL = 'https://api.shopware.com';

    /**
     * Check extension compatibility for Shopware upgrade (public API, no auth required)
     *
     * @param array<array{name: string, version: string}> $extensions
     * @return array<array{name: string, label: string, iconPath: string, status: array}>
     */
    public static function checkExtensionUpdates(
        string $currentVersion,
        string $futureVersion,
        array $extensions
    ): array {
        $httpClient = new HttpClient([
            'base_uri' => self::BASE_URL,
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ]);

        $body = [
            'futureShopwareVersion' => $futureVersion,
            'plugins' => array_map(function (array $ext) {
                return [
                    'name' => $ext['name'],
                    'version' => $ext['version'],
                ];
            }, $extensions),
        ];

        $response = $httpClient->post(
            "/swplatform/autoupdate?language=en-GB&shopwareVersion=$currentVersion",
            ['json' => $body]
        );

        return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }
}
