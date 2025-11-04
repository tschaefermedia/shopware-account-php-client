<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use TschaeferMedia\ShopwareAccountApi\Client;

// Login
$client = Client::login(
    email: 'your-email@example.com',
    password: 'your-password'
);

// Get merchant endpoint
$merchant = $client->merchant();

// List all shops
$shops = $merchant->getShops();

echo "Found " . count($shops) . " shops:\n\n";

foreach ($shops as $shop) {
    echo "Shop: {$shop->domain}\n";
    echo "  ID: {$shop->id}\n";
    echo "  Environment: {$shop->environment['name']}\n";
    echo "  Shopware Version: {$shop->shopwareVersion['name']}\n";
    echo "  Edition: {$shop->shopwareEdition}\n";
    echo "  Activated: " . ($shop->activated ? 'Yes' : 'No') . "\n";

    // Get or generate composer token
    try {
        $token = $merchant->getComposerToken($shop->id);
        echo "  Composer Token: $token\n";
    } catch (\Exception $e) {
        echo "  Composer Token: Not set\n";
        echo "  Generating new token...\n";
        $token = $merchant->generateComposerToken($shop->id);
        echo "  New Composer Token: $token\n";
    }

    echo "\n";
}
