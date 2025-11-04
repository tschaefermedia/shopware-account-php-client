<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use TschaeferMedia\ShopwareAccountApi\Client;

// Login with email and password
$client = Client::login(
    email: 'your-email@example.com',
    password: 'your-password'
);

// Get user profile
$profile = $client->getProfile();
echo "Logged in as: {$profile->email}\n";

// List all memberships (companies)
echo "\nMemberships:\n";
foreach ($client->getMemberships() as $membership) {
    $company = $membership->company;
    $active = $membership->active ? '(active)' : '';
    echo "- {$company['name']} (ID: {$company['id']}) $active\n";
}

// Get active membership details
$activeMembership = $client->getActiveMembership();
if ($activeMembership) {
    echo "\nActive company: {$activeMembership->company['name']}\n";
    echo "Roles: " . implode(', ', $activeMembership->getRoles()) . "\n";
}
