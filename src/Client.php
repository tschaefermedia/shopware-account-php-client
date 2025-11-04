<?php

declare(strict_types=1);

namespace TschaeferMedia\ShopwareAccountApi;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use TschaeferMedia\ShopwareAccountApi\Authentication\Token;
use TschaeferMedia\ShopwareAccountApi\Endpoints\MerchantEndpoint;
use TschaeferMedia\ShopwareAccountApi\Endpoints\ProducerEndpoint;
use TschaeferMedia\ShopwareAccountApi\Exception\ApiException;
use TschaeferMedia\ShopwareAccountApi\Exception\AuthenticationException;
use TschaeferMedia\ShopwareAccountApi\Models\Membership;
use TschaeferMedia\ShopwareAccountApi\Models\Profile;
use TschaeferMedia\ShopwareAccountApi\Validation\Validator;

class Client
{
    private const BASE_URL = 'https://api.shopware.com';
    private const CACHE_DIR = '.cache/shopware-account-api';
    private const TOKEN_CACHE_FILE = 'token.json';

    private HttpClient $httpClient;
    private ?Token $token = null;

    /** @var Membership[] */
    private array $memberships = [];
    private ?Membership $activeMembership = null;

    public function __construct(
        private readonly string $cacheDir = '',
    ) {
        $this->httpClient = new HttpClient([
            'base_uri' => self::BASE_URL,
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'user-agent' => 'shopware-account-api-php/1.0',
            ],
            'timeout' => 30.0,
            'connect_timeout' => 10.0,
            'http_errors' => true,
            'allow_redirects' => [
                'max' => 5,
                'strict' => true,
            ],
        ]);
    }

    /**
     * Login with email and password
     */
    public static function login(string $email, string $password, string $cacheDir = ''): self
    {
        Validator::email($email, 'Email');
        Validator::notEmpty($password, 'Password');

        $client = new self($cacheDir);

        // Try to load from cache first
        if ($cachedClient = $client->loadFromCache()) {
            return $cachedClient;
        }

        // Perform login
        try {
            $response = $client->httpClient->post('/accesstokens', [
                'json' => [
                    'shopwareId' => $email,
                    'password' => $password,
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $client->token = Token::fromApiResponse($data);
        } catch (GuzzleException $e) {
            throw new AuthenticationException(
                'Login failed: ' . $e->getMessage(),
                $e->getCode(),
                previous: $e
            );
        }

        // Fetch memberships
        $client->fetchMemberships();

        // Save to cache
        $client->saveToCache();

        return $client;
    }

    /**
     * Get user profile
     */
    public function getProfile(): Profile
    {
        $this->ensureAuthenticated();
        assert($this->token !== null);

        $data = $this->request('GET', sprintf('/account/%d', $this->token->userAccountId));

        return Profile::fromArray($data);
    }

    /**
     * Get all memberships
     *
     * @return Membership[]
     */
    public function getMemberships(): array
    {
        return $this->memberships;
    }

    /**
     * Get active membership
     */
    public function getActiveMembership(): ?Membership
    {
        return $this->activeMembership;
    }

    /**
     * Get active company ID
     */
    public function getActiveCompanyId(): int
    {
        return $this->activeMembership?->company['id'] ?? 0;
    }

    /**
     * Get user account ID
     */
    public function getUserId(): int
    {
        return $this->token?->userAccountId ?? 0;
    }

    /**
     * Change active membership
     */
    public function changeActiveMembership(Membership $membership): void
    {
        $this->ensureAuthenticated();
        assert($this->token !== null);

        $this->request('POST', sprintf('/account/%d/memberships/change', $this->token->userAccountId), [
            'membershipId' => $membership->id,
        ]);

        $this->activeMembership = $membership;
        $this->saveToCache();
    }

    /**
     * Get producer endpoint
     */
    public function producer(): ProducerEndpoint
    {
        $this->ensureAuthenticated();

        $companyId = $this->getActiveCompanyId();
        $allocations = $this->request('GET', sprintf('/companies/%d/allocations', $companyId));

        if (empty($allocations)) {
            throw new ApiException('Company is not unlocked as producer');
        }

        $producerId = $allocations[0]['id'] ?? 0;

        return new ProducerEndpoint($this, $producerId);
    }

    /**
     * Get merchant endpoint
     */
    public function merchant(): MerchantEndpoint
    {
        return new MerchantEndpoint($this);
    }

    /**
     * Make authenticated HTTP request
     *
     * @throws ApiException
     */
    public function request(string $method, string $path, ?array $body = null): mixed
    {
        $this->ensureAuthenticated();
        assert($this->token !== null);

        $options = [
            'headers' => [
                'x-shopware-token' => $this->token->token,
            ],
        ];

        if ($body !== null) {
            $options['json'] = $body;
        }

        try {
            $response = $this->httpClient->request($method, $path, $options);
            $content = (string) $response->getBody();

            if (empty($content)) {
                return null;
            }

            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (RequestException $e) {
            $statusCode = $e->getCode();
            $message = $e->getMessage();

            if ($e->hasResponse() && ($response = $e->getResponse()) !== null) {
                $responseBody = (string) $response->getBody();

                throw new ApiException("API request failed: $message", $statusCode, $responseBody, $e);
            }

            throw new ApiException("API request failed: $message", $statusCode, previous: $e);
        } catch (GuzzleException $e) {
            throw new ApiException("API request failed: " . $e->getMessage(), $e->getCode(), previous: $e);
        }
    }

    /**
     * Upload file via multipart form data
     *
     * @throws ApiException
     */
    public function uploadFile(string $path, string $filePath, string $fieldName = 'file', array $additionalFields = []): mixed
    {
        Validator::fileReadable($filePath, 'Upload file');

        $this->ensureAuthenticated();
        assert($this->token !== null);

        $multipart = [
            [
                'name' => $fieldName,
                'contents' => fopen($filePath, 'r'),
                'filename' => basename($filePath),
            ],
        ];

        foreach ($additionalFields as $name => $contents) {
            $multipart[] = [
                'name' => $name,
                'contents' => $contents,
            ];
        }

        try {
            $response = $this->httpClient->post($path, [
                'headers' => [
                    'x-shopware-token' => $this->token->token,
                ],
                'multipart' => $multipart,
            ]);

            $content = (string) $response->getBody();

            if (empty($content)) {
                return null;
            }

            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $e) {
            throw new ApiException('File upload failed: ' . $e->getMessage(), $e->getCode(), previous: $e);
        }
    }

    /**
     * Invalidate token cache (logout)
     */
    public function logout(): void
    {
        $cacheFile = $this->getCacheFilePath();
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }

        $this->token = null;
        $this->memberships = [];
        $this->activeMembership = null;
    }

    private function ensureAuthenticated(): void
    {
        if ($this->token === null) {
            throw new AuthenticationException('Not authenticated. Call login() first.');
        }

        if (!$this->token->isValid()) {
            throw new AuthenticationException('Token expired. Please login again.');
        }
    }

    private function fetchMemberships(): void
    {
        assert($this->token !== null);

        $data = $this->request('GET', sprintf('/account/%d/memberships', $this->token->userAccountId));

        $this->memberships = array_map(
            fn (array $m) => Membership::fromArray($m),
            $data
        );

        // Set active membership
        foreach ($this->memberships as $membership) {
            if ($membership->active) {
                $this->activeMembership = $membership;

                break;
            }
        }
    }

    private function getCacheFilePath(): string
    {
        $baseDir = $this->cacheDir ?: (getenv('HOME') ?: getenv('USERPROFILE')) . '/' . self::CACHE_DIR;

        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0o700, true);
        }

        return $baseDir . '/' . self::TOKEN_CACHE_FILE;
    }

    private function loadFromCache(): ?self
    {
        $cacheFile = $this->getCacheFilePath();

        if (!file_exists($cacheFile)) {
            return null;
        }

        try {
            $contents = file_get_contents($cacheFile);
            if ($contents === false) {
                return null;
            }

            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

            $token = Token::fromApiResponse($data['token']);

            if (!$token->isValid()) {
                return null;
            }

            $this->token = $token;
            $this->memberships = array_map(
                fn (array $m) => Membership::fromArray($m),
                $data['memberships'] ?? []
            );

            if (isset($data['activeMembership'])) {
                $this->activeMembership = Membership::fromArray($data['activeMembership']);
            }

            return $this;
        } catch (\Throwable) {
            // Cache corrupted, ignore
            return null;
        }
    }

    private function saveToCache(): void
    {
        assert($this->token !== null);

        $cacheFile = $this->getCacheFilePath();

        $data = [
            'token' => $this->token->toArray(),
            'memberships' => array_map(fn (Membership $m) => $m->toArray(), $this->memberships),
            'activeMembership' => $this->activeMembership?->toArray(),
        ];

        file_put_contents($cacheFile, json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        chmod($cacheFile, 0o600);
    }
}
