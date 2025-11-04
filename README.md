# Shopware Account API - PHP Client

Modern PHP 8.1+ client library for the Shopware Account API. Manage extensions, shops, and account resources programmatically.

## Features

- **Modern PHP 8.1+** with typed properties, readonly classes, and enums
- **Complete API Coverage** - All 29+ API methods implemented
- **Automatic Token Caching** - Login once, token cached automatically
- **Type-Safe Models** - Full type hints for IDE autocomplete
- **PSR-7 Compatible** - Built on Guzzle HTTP client
- **Zero Dependencies** (except Guzzle)

## Installation

```bash
composer require shopware/account-api-client
```

## Quick Start

### Authentication

```php
use Shopware\AccountApi\Client;

// Login (token cached automatically)
$client = Client::login(
    email: 'your-email@example.com',
    password: 'your-password'
);

// Get user profile
$profile = $client->getProfile();
echo "Logged in as: {$profile->email}\n";

// Logout (clear token cache)
$client->logout();
```

### Managing Extensions

```php
// Get producer endpoint
$producer = $client->producer();

// List all extensions
$extensions = $producer->getExtensions();

// Get specific extension
$extension = $producer->getExtensionByName('SwagExamplePlugin');

// Get extension binaries
$binaries = $producer->getExtensionBinaries($extension->id);
```

### Uploading Extension Version

```php
// Create new version
$binary = $producer->createExtensionBinary(
    extensionId: $extension->id,
    version: '1.2.0',
    softwareVersions: ['6.5.0', '6.5.1'],
    changelogs: [
        ['locale' => 'en_GB', 'text' => 'Bug fixes and improvements'],
        ['locale' => 'de_DE', 'text' => 'Fehlerbehebungen und Verbesserungen'],
    ]
);

// Upload ZIP file
$producer->uploadExtensionBinaryFile(
    extensionId: $extension->id,
    binaryId: $binary->id,
    zipPath: '/path/to/plugin.zip'
);

// Trigger code review
$producer->triggerCodeReview($extension->id);

// Check review results
$results = $producer->getBinaryReviewResults($extension->id, $binary->id);
foreach ($results as $result) {
    if ($result->hasPassed()) {
        echo "✓ Code review passed!\n";
    }
}
```

### Managing Merchant Shops

```php
// Get merchant endpoint
$merchant = $client->merchant();

// List all shops
$shops = $merchant->getShops();

foreach ($shops as $shop) {
    echo "{$shop->domain} - Shopware {$shop->shopwareVersion['name']}\n";
}

// Get/generate composer token
$token = $merchant->getComposerToken($shop->id);
// or
$token = $merchant->generateComposerToken($shop->id);
```

### Managing Extension Media

```php
// Update extension icon
$producer->updateExtensionIcon($extension->id, '/path/to/icon.png');

// Add gallery images
$image = $producer->addExtensionImage($extension->id, '/path/to/screenshot.png');

// Update image details
$image->details[0]['caption'] = 'Main interface';
$image->details[0]['preview'] = true;
$producer->updateExtensionImage($extension->id, $image);

// List all images
$images = $producer->getExtensionImages($extension->id);

// Delete image
$producer->deleteExtensionImage($extension->id, $imageId);
```

### Company/Membership Management

```php
// List all memberships
foreach ($client->getMemberships() as $membership) {
    echo "{$membership->company['name']} - ";
    echo implode(', ', $membership->getRoles()) . "\n";
}

// Switch company
$membership = $client->getMemberships()[1]; // Select another company
$client->changeActiveMembership($membership);
```

### Check Extension Compatibility

```php
use Shopware\AccountApi\UpdateCompatibility;

// Check compatibility for Shopware upgrade (no auth required)
$compatibility = UpdateCompatibility::checkExtensionUpdates(
    currentVersion: '6.5.0',
    futureVersion: '6.6.0',
    extensions: [
        ['name' => 'SwagExample', 'version' => '1.0.0'],
        ['name' => 'SwagPayment', 'version' => '2.1.3'],
    ]
);

foreach ($compatibility as $ext) {
    $status = $ext['status']['type']; // 'success', 'warning', 'error'
    echo "{$ext['name']}: {$ext['status']['label']}\n";
}
```

## API Reference

### Client

| Method | Return Type | Description |
|--------|-------------|-------------|
| `login($email, $password)` | `Client` | Login and create authenticated client |
| `getProfile()` | `Profile` | Get user profile |
| `getMemberships()` | `Membership[]` | List all company memberships |
| `getActiveMembership()` | `Membership` | Get currently active membership |
| `changeActiveMembership($membership)` | `void` | Switch active company |
| `producer()` | `ProducerEndpoint` | Get producer API endpoint |
| `merchant()` | `MerchantEndpoint` | Get merchant API endpoint |
| `logout()` | `void` | Clear cached token |

### ProducerEndpoint

#### Extension Management

| Method | Return Type | Description |
|--------|-------------|-------------|
| `getProfile()` | `Producer` | Get producer profile |
| `getExtensions()` | `Extension[]` | List extensions with filtering |
| `getExtensionByName($name)` | `?Extension` | Find extension by name |
| `getExtensionById($id)` | `Extension` | Get extension by ID |
| `updateExtension($extension)` | `void` | Update extension metadata |
| `getSoftwareVersions($generation)` | `array` | Get Shopware versions |
| `getExtensionGeneralInfo()` | `array` | Get categories, locales, etc. |

#### Binary Management

| Method | Return Type | Description |
|--------|-------------|-------------|
| `getExtensionBinaries($extensionId)` | `ExtensionBinary[]` | List binary versions |
| `createExtensionBinary($extensionId, ...)` | `ExtensionBinary` | Create new version |
| `updateExtensionBinaryInfo($extensionId, ...)` | `void` | Update version metadata |
| `uploadExtensionBinaryFile($extensionId, $binaryId, $zipPath)` | `void` | Upload ZIP file |

#### Media Management

| Method | Return Type | Description |
|--------|-------------|-------------|
| `updateExtensionIcon($extensionId, $iconPath)` | `void` | Update extension icon |
| `getExtensionImages($extensionId)` | `ExtensionImage[]` | List gallery images |
| `addExtensionImage($extensionId, $imagePath)` | `ExtensionImage` | Add gallery image |
| `updateExtensionImage($extensionId, $image)` | `void` | Update image details |
| `deleteExtensionImage($extensionId, $imageId)` | `void` | Remove image |

#### Code Review

| Method | Return Type | Description |
|--------|-------------|-------------|
| `triggerCodeReview($extensionId)` | `void` | Start automated review |
| `getBinaryReviewResults($extensionId, $binaryId)` | `BinaryReviewResult[]` | Get review results |

### MerchantEndpoint

| Method | Return Type | Description |
|--------|-------------|-------------|
| `getShops()` | `MerchantShop[]` | List merchant shops |
| `getShopByDomain($domain)` | `?MerchantShop` | Find shop by domain |
| `getComposerToken($shopId)` | `string` | Get composer token |
| `generateComposerToken($shopId)` | `string` | Generate new token |
| `saveComposerToken($shopId, $token)` | `void` | Save token |

### UpdateCompatibility

| Method | Return Type | Description |
|--------|-------------|-------------|
| `checkExtensionUpdates($current, $future, $extensions)` | `array` | Check compatibility (static, no auth) |

## Data Models

### Profile

```php
class Profile {
    public readonly int $id;
    public readonly string $email;
    public readonly string $creationDate;
    public readonly bool $banned;
    public readonly bool $verified;
    public readonly array $personalData;
    public readonly bool $partnerMarketingOptIn;
    public readonly ?array $selectedMembership;
}
```

### Membership

```php
class Membership {
    public readonly int $id;
    public readonly string $creationDate;
    public readonly bool $active;
    public readonly array $member;
    public readonly array $company;
    public readonly array $roles;

    public function getRoles(): array; // Returns role names
}
```

### Extension

```php
class Extension {
    public int $id;
    public string $name;
    public string $code;
    public array $producer;
    public array $type;
    public array $lifecycleStatus;
    public array $generation;
    public array $infos; // Localized descriptions
    public array $categories;
    // ... many more properties
}
```

### ExtensionBinary

```php
class ExtensionBinary {
    public readonly int $id;
    public readonly string $version;
    public readonly array $status;
    public readonly array $compatibleSoftwareVersions;
    public readonly array $changelogs;
    public readonly bool $ionCubeEncrypted;
    public readonly bool $licenseCheckRequired;
    public readonly bool $hasActiveCodeReviewWarnings;
}
```

### ExtensionImage

```php
class ExtensionImage {
    public readonly int $id;
    public readonly string $remoteLink;
    public array $details; // Localized captions
    public int $priority;
}
```

### MerchantShop

```php
class MerchantShop {
    public readonly int $id;
    public readonly string $domain;
    public readonly string $type;
    public readonly array $shopwareVersion;
    public readonly string $shopwareEdition;
    public readonly array $environment;
    public readonly bool $activated;
    // ... many more properties
}
```

### BinaryReviewResult

```php
class BinaryReviewResult {
    public readonly int $id;
    public readonly array $type;
    public readonly string $message;
    public readonly array $subCheckResults;

    public function hasPassed(): bool;
    public function hasWarnings(): bool;
    public function isPending(): bool;
    public function getSummary(): string; // Failed checks summary
}
```

## Configuration

### Custom Cache Directory

```php
$client = Client::login(
    email: 'your-email@example.com',
    password: 'your-password',
    cacheDir: '/custom/cache/path'
);
```

Default cache location: `~/.cache/shopware-account-api/token.json`

## Error Handling

```php
use Shopware\AccountApi\Exception\ApiException;
use Shopware\AccountApi\Exception\AuthenticationException;

try {
    $client = Client::login($email, $password);
} catch (AuthenticationException $e) {
    echo "Login failed: {$e->getMessage()}\n";
} catch (ApiException $e) {
    echo "API error: {$e->getMessage()}\n";
    echo "Status code: {$e->statusCode}\n";
    echo "Response: {$e->responseBody}\n";
}
```

## Examples

See the [`examples/`](examples/) directory for complete working examples:

- [`login.php`](examples/login.php) - Authentication and profile management
- [`upload-extension.php`](examples/upload-extension.php) - Complete extension upload workflow
- [`list-shops.php`](examples/list-shops.php) - Shop management and composer tokens
- [`check-compatibility.php`](examples/check-compatibility.php) - Extension compatibility checking
- [`manage-images.php`](examples/manage-images.php) - Icon and gallery management

## Requirements

- PHP 8.1 or higher
- ext-json
- guzzlehttp/guzzle ^7.8

## Development

### Install Dependencies

```bash
composer install
```

### Run Examples

```bash
php examples/login.php
```

## API Documentation

For detailed API documentation, see [SHOPWARE_ACCOUNT_API.md](../SHOPWARE_ACCOUNT_API.md)

## License

MIT License

## Related Projects

- [shopware-cli](../) - Official Shopware CLI tool (Go)
- [Shopware Account API Documentation](../SHOPWARE_ACCOUNT_API.md)

## Support

For issues and questions:
- Open an issue on GitHub
- Check the [examples](examples/) directory
- Read the [API documentation](../SHOPWARE_ACCOUNT_API.md)

---

**Base URL:** `https://api.shopware.com`

**Total API Methods:** 29+ methods across 3 endpoints

**Authentication:** JWT token-based with automatic caching
