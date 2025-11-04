<?php

declare(strict_types=1);

namespace Shopware\AccountApi\Endpoints;

use Shopware\AccountApi\Client;
use Shopware\AccountApi\Models\BinaryReviewResult;
use Shopware\AccountApi\Models\Extension;
use Shopware\AccountApi\Models\ExtensionBinary;
use Shopware\AccountApi\Models\ExtensionImage;
use Shopware\AccountApi\Models\Producer;
use Shopware\AccountApi\Validation\Validator;

class ProducerEndpoint
{
    public function __construct(
        private readonly Client $client,
        private readonly int $producerId,
    ) {
    }

    /**
     * Get producer profile
     */
    public function getProfile(): Producer
    {
        $data = $this->client->request(
            'GET',
            sprintf('/producers?companyId=%d', $this->client->getActiveCompanyId())
        );

        return Producer::fromArray($data[0] ?? []);
    }

    /**
     * Get list of extensions
     *
     * @return Extension[]
     */
    public function getExtensions(
        int $limit = 50,
        int $offset = 0,
        ?string $orderBy = null,
        ?string $orderSequence = null,
        ?string $search = null
    ): array {
        $query = [
            'producerId' => $this->producerId,
            'limit' => $limit,
            'offset' => $offset,
        ];

        if ($orderBy) {
            $query['orderBy'] = $orderBy;
        }
        if ($orderSequence) {
            $query['orderSequence'] = $orderSequence;
        }
        if ($search) {
            $query['search'] = $search;
        }

        $queryString = http_build_query($query);
        $data = $this->client->request('GET', "/plugins?$queryString");

        return array_map(fn (array $item) => Extension::fromArray($item), $data);
    }

    /**
     * Get extension by name
     */
    public function getExtensionByName(string $name): ?Extension
    {
        Validator::notEmpty($name, 'Extension name');

        $extensions = $this->getExtensions(limit: 1, search: $name);

        foreach ($extensions as $extension) {
            if ($extension->name === $name) {
                return $extension;
            }
        }

        return null;
    }

    /**
     * Get extension by ID
     */
    public function getExtensionById(int $id): Extension
    {
        $data = $this->client->request('GET', "/plugins/$id");

        return Extension::fromArray($data);
    }

    /**
     * Update extension
     */
    public function updateExtension(Extension $extension): void
    {
        $this->client->request('PUT', "/plugins/{$extension->id}", $extension->toArray());
    }

    /**
     * Get Shopware software versions
     */
    public function getSoftwareVersions(string $generation = 'classic'): array
    {
        $filter = json_encode([
            ['property' => 'parent', 'value' => null],
            ['property' => 'selectable', 'value' => true],
            ['property' => 'name', 'value' => $generation, 'operator' => 'LIKE'],
        ], JSON_THROW_ON_ERROR);

        $data = $this->client->request('GET', "/pluginstatics/softwareVersions?filter=$filter");

        return $data;
    }

    /**
     * Get extension general information (categories, statuses, locales, etc.)
     */
    public function getExtensionGeneralInfo(): array
    {
        return $this->client->request('GET', '/pluginstatics/all');
    }

    /**
     * Get extension binaries
     *
     * @return ExtensionBinary[]
     */
    public function getExtensionBinaries(int $extensionId): array
    {
        $data = $this->client->request(
            'GET',
            "/producers/{$this->producerId}/plugins/$extensionId/binaries"
        );

        return array_map(fn (array $item) => ExtensionBinary::fromArray($item), $data);
    }

    /**
     * Create extension binary
     */
    public function createExtensionBinary(
        int $extensionId,
        string $version,
        array $softwareVersions,
        array $changelogs = []
    ): ExtensionBinary {
        Validator::positive($extensionId, 'Extension ID');
        Validator::version($version, 'Version');
        Validator::notEmptyArray($softwareVersions, 'Software versions');

        $data = $this->client->request(
            'POST',
            "/producers/{$this->producerId}/plugins/$extensionId/binaries",
            [
                'version' => $version,
                'softwareVersions' => $softwareVersions,
                'changelogs' => $changelogs,
            ]
        );

        return ExtensionBinary::fromArray($data);
    }

    /**
     * Update extension binary info
     */
    public function updateExtensionBinaryInfo(
        int $extensionId,
        int $binaryId,
        array $softwareVersions,
        bool $ionCubeEncrypted = false,
        bool $licenseCheckRequired = false,
        array $changelogs = []
    ): void {
        $this->client->request(
            'PUT',
            "/producers/{$this->producerId}/plugins/$extensionId/binaries/$binaryId",
            [
                'id' => $binaryId,
                'softwareVersions' => $softwareVersions,
                'ionCubeEncrypted' => $ionCubeEncrypted,
                'licenseCheckRequired' => $licenseCheckRequired,
                'changelogs' => $changelogs,
            ]
        );
    }

    /**
     * Upload extension binary file (ZIP)
     */
    public function uploadExtensionBinaryFile(int $extensionId, int $binaryId, string $zipPath): void
    {
        Validator::positive($extensionId, 'Extension ID');
        Validator::positive($binaryId, 'Binary ID');
        Validator::fileReadable($zipPath, 'ZIP file');

        $this->client->uploadFile(
            "/producers/{$this->producerId}/plugins/$extensionId/binaries/$binaryId/file",
            $zipPath
        );
    }

    /**
     * Update extension icon
     */
    public function updateExtensionIcon(int $extensionId, string $iconPath): void
    {
        // Resize icon to 256x256 if needed
        $processedIcon = $this->resizeIcon($iconPath);

        $this->client->uploadFile(
            "/plugins/$extensionId/icon",
            $processedIcon,
            'file'
        );

        // Clean up temp file if we created one
        if ($processedIcon !== $iconPath) {
            unlink($processedIcon);
        }
    }

    /**
     * Get extension images
     *
     * @return ExtensionImage[]
     */
    public function getExtensionImages(int $extensionId): array
    {
        $data = $this->client->request('GET', "/plugins/$extensionId/pictures");

        return array_map(fn (array $item) => ExtensionImage::fromArray($item), $data);
    }

    /**
     * Add extension image
     */
    public function addExtensionImage(int $extensionId, string $imagePath): ExtensionImage
    {
        $data = $this->client->uploadFile(
            "/plugins/$extensionId/pictures",
            $imagePath
        );

        return ExtensionImage::fromArray($data);
    }

    /**
     * Update extension image
     */
    public function updateExtensionImage(int $extensionId, ExtensionImage $image): void
    {
        $this->client->request(
            'PUT',
            "/plugins/$extensionId/pictures/{$image->id}",
            $image->toArray()
        );
    }

    /**
     * Delete extension image
     */
    public function deleteExtensionImage(int $extensionId, int $imageId): void
    {
        $this->client->request('DELETE', "/plugins/$extensionId/pictures/$imageId");
    }

    /**
     * Trigger code review for extension
     */
    public function triggerCodeReview(int $extensionId): void
    {
        $this->client->request('POST', "/plugins/$extensionId/reviews");
    }

    /**
     * Get binary review results
     *
     * @return BinaryReviewResult[]
     */
    public function getBinaryReviewResults(int $extensionId, int $binaryId): array
    {
        $data = $this->client->request(
            'GET',
            "/plugins/$extensionId/binaries/$binaryId/checkresults"
        );

        return array_map(fn (array $item) => BinaryReviewResult::fromArray($item), $data);
    }

    private function resizeIcon(string $iconPath): string
    {
        if (!function_exists('imagecreatefromstring')) {
            // GD not available, return as-is
            return $iconPath;
        }

        $contents = file_get_contents($iconPath);
        if ($contents === false) {
            return $iconPath;
        }

        $image = imagecreatefromstring($contents);
        if ($image === false) {
            return $iconPath;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // Already 256x256
        if ($width === 256 && $height === 256) {
            imagedestroy($image);

            return $iconPath;
        }

        // Resize to 256x256
        $resized = imagecreatetruecolor(256, 256);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, 256, 256, $width, $height);

        // Save to temp file
        $tempFile = sys_get_temp_dir() . '/shopware-icon-' . uniqid() . '.png';
        imagepng($resized, $tempFile);

        imagedestroy($image);
        imagedestroy($resized);

        return $tempFile;
    }
}
