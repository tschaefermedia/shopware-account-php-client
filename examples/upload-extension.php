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

// Get extension by name
$extension = $producer->getExtensionByName('SwagExamplePlugin');

if (!$extension) {
    echo "Extension not found!\n";
    exit(1);
}

echo "Found extension: {$extension->name} (ID: {$extension->id})\n";

// Get existing binaries
$binaries = $producer->getExtensionBinaries($extension->id);
echo "Existing versions: " . count($binaries) . "\n";

// Create new binary version
echo "\nCreating new version 1.2.0...\n";
$binary = $producer->createExtensionBinary(
    extensionId: $extension->id,
    version: '1.2.0',
    softwareVersions: ['6.5.0', '6.5.1', '6.5.2'],
    changelogs: [
        ['locale' => 'en_GB', 'text' => 'Added new features and bug fixes'],
        ['locale' => 'de_DE', 'text' => 'Neue Funktionen und Fehlerbehebungen'],
    ]
);

echo "Binary created with ID: {$binary->id}\n";

// Upload ZIP file
echo "Uploading ZIP file...\n";
$producer->uploadExtensionBinaryFile(
    extensionId: $extension->id,
    binaryId: $binary->id,
    zipPath: '/path/to/your/plugin.zip'
);

echo "Upload complete!\n";

// Trigger code review
echo "Triggering code review...\n";
$producer->triggerCodeReview($extension->id);

// Wait a bit and check results
sleep(5);

echo "Checking code review results...\n";
$results = $producer->getBinaryReviewResults($extension->id, $binary->id);

foreach ($results as $result) {
    if ($result->hasPassed()) {
        echo "✓ Code review passed!\n";
    } elseif ($result->isPending()) {
        echo "⏳ Code review pending...\n";
    } else {
        echo "✗ Code review failed:\n";
        echo $result->getSummary() . "\n";
    }
}
