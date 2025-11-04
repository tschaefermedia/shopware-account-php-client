<?php

declare(strict_types=1);

namespace Shopware\AccountApi\Endpoints;

use Shopware\AccountApi\Client;
use Shopware\AccountApi\Models\MerchantShop;

class MerchantEndpoint
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    /**
     * Get list of merchant shops
     *
     * @return MerchantShop[]
     */
    public function getShops(): array
    {
        $companyId = $this->client->getActiveCompanyId();
        $data = $this->client->request('GET', "/shops?limit=100&userId=$companyId");

        return array_map(fn (array $item) => MerchantShop::fromArray($item), $data);
    }

    /**
     * Get shop by domain
     */
    public function getShopByDomain(string $domain): ?MerchantShop
    {
        $shops = $this->getShops();

        foreach ($shops as $shop) {
            if ($shop->domain === $domain || $shop->domainIdn === $domain) {
                return $shop;
            }
        }

        return null;
    }

    /**
     * Get composer token for shop
     */
    public function getComposerToken(int $shopId): string
    {
        $companyId = $this->client->getActiveCompanyId();
        $data = $this->client->request(
            'GET',
            "/companies/$companyId/shops/$shopId/packagestoken"
        );

        return $data['token'] ?? '';
    }

    /**
     * Generate new composer token for shop
     */
    public function generateComposerToken(int $shopId): string
    {
        $companyId = $this->client->getActiveCompanyId();
        $data = $this->client->request(
            'POST',
            "/companies/$companyId/shops/$shopId/packagestoken"
        );

        return $data['token'] ?? '';
    }

    /**
     * Save composer token for shop
     */
    public function saveComposerToken(int $shopId, string $token): void
    {
        $companyId = $this->client->getActiveCompanyId();
        $this->client->request(
            'POST',
            "/companies/$companyId/shops/$shopId/packagestoken/$token"
        );
    }
}
