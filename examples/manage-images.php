<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Shopware\AccountApi\Client;

// Login
$client = Client::login(
    email: 'your-email@example.com',
    password: 'your-password'
);

// Get producer endpoint
$producer = $client->producer();

// Get extension
$extension = $producer->getExtensionByName('SwagExamplePlugin');

if (!$extension) {
    echo "Extension not found!\n";
    exit(1);
}

echo "Managing media for: {$extension->name}\n\n";

// Update icon
echo "Updating extension icon...\n";
$producer->updateExtensionIcon($extension->id, '/path/to/icon.png');
echo "Icon updated!\n\n";

// Get existing images
$images = $producer->getExtensionImages($extension->id);
echo "Existing images: " . count($images) . "\n\n";

// Add new gallery images
echo "Adding gallery images...\n";
$image1 = $producer->addExtensionImage($extension->id, '/path/to/screenshot1.png');
$image2 = $producer->addExtensionImage($extension->id, '/path/to/screenshot2.png');

echo "Added 2 images\n\n";

// Update image details
echo "Setting image captions...\n";
if (!empty($image1->details)) {
    $image1->details[0]['caption'] = 'Main interface screenshot';
    $image1->details[0]['preview'] = true;
    $image1->details[0]['activated'] = true;
    $producer->updateExtensionImage($extension->id, $image1);
}

if (!empty($image2->details)) {
    $image2->details[0]['caption'] = 'Settings page';
    $image2->details[0]['activated'] = true;
    $producer->updateExtensionImage($extension->id, $image2);
}

echo "Image details updated!\n\n";

// List all images
$allImages = $producer->getExtensionImages($extension->id);
echo "All images (" . count($allImages) . "):\n";
foreach ($allImages as $img) {
    foreach ($img->details as $detail) {
        $preview = $detail['preview'] ? ' (preview)' : '';
        echo "- ID {$img->id}: {$detail['caption']}$preview\n";
    }
}
