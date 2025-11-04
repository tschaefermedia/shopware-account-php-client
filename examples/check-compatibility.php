<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use TschaeferMedia\ShopwareAccountApi\UpdateCompatibility;

// Check extension compatibility for Shopware upgrade
$compatibility = UpdateCompatibility::checkExtensionUpdates(
    currentVersion: '6.5.0',
    futureVersion: '6.6.0',
    extensions: [
        ['name' => 'SwagExamplePlugin', 'version' => '1.0.0'],
        ['name' => 'SwagPayment', 'version' => '2.1.3'],
        ['name' => 'SwagCustomProducts', 'version' => '3.0.1'],
    ]
);

echo "Extension Compatibility Check: 6.5.0 → 6.6.0\n\n";

foreach ($compatibility as $ext) {
    $status = $ext['status']['type'] ?? 'unknown';
    $statusLabel = $ext['status']['label'] ?? 'Unknown';

    $icon = match ($status) {
        'success' => '✓',
        'warning' => '⚠',
        'error' => '✗',
        default => '?',
    };

    echo "$icon {$ext['name']}: $statusLabel\n";
}
