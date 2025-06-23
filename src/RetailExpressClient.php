<?php

namespace RetailExpress;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Retail Express API PHP Client
 *
 * Supports API v2.1 and handles token-based authentication.
 *
 * @link https://developer.retailexpress.com.au/getting-started
 * @link https://developer.retailexpress.com.au/api-details#api=retail-express-api-v21
 */
class RetailExpressClient
{
    private string $apiKey;
    private ClientInterface $client;

    private ?string $accessToken = null;
    private ?\DateTimeImmutable $tokenExpires = null;

    /**
     * Constructor
     *
     * @param string $apiKey Your permanent Retail Express API key (for the x-api-key header)
     * @param ClientInterface|null $client An optional Guzzle client instance for testing.
     * @param string $base Base URL for the API (default: Retail Express API URL)
     * @param string $version API version for all endpoints (default: v2.1)
     */
    public function __construct(string $apiKey, ClientInterface $client = null, string $base = 'https://api.retailexpress.com.au', string $version = 'v2.1')
    {
        $this->apiKey = $apiKey;

        // Use the provided Guzzle client, or create a new one.
        $this->client = $client ?: new Client([
            'base_uri' => rtrim($base, '/') . '/' . $version . '/',
            'timeout' => 30,
        ]);
    }

    /**
     * Fetches a new access token from the authentication endpoint.
     *
     * @throws \RuntimeException on authentication failure
     */
    private function authenticate(): void
    {
        try {
            // No need for a separate auth client, just call the endpoint.
            $response = $this->client->request('GET', 'auth/token', [
                'headers' => [
                    'x-api-key' => $this->apiKey,
                    'Cache-Control' => 'no-cache',
                ]
            ]);

            $data = json_decode((string) $response->getBody(), true);

            if (empty($data['access_token']) || empty($data['expires_on'])) {
                throw new \RuntimeException('Authentication failed: Invalid token response from API.');
            }

            $this->accessToken = $data['access_token'];
            $this->tokenExpires = (new \DateTimeImmutable($data['expires_on']))->sub(new \DateInterval('PT60S'));
        } catch (RequestException $e) {
            $error = $e->hasResponse() ? (string) $e->getResponse()->getBody() : $e->getMessage();
            throw new \RuntimeException("API authentication request failed: {$error}");
        }
    }

    /**
     * Checks if the current token is valid and fetches a new one if not.
     *
     * @return string The valid access token.
     */
    private function getValidAccessToken(): string
    {
        if ($this->accessToken === null || new \DateTimeImmutable() >= $this->tokenExpires) {
            $this->authenticate();
        }
        return $this->accessToken;
    }

    /**
     * Generic HTTP request wrapper
     *
     * @param string $method GET|POST|PUT|DELETE
     * @param string $uri Relative endpoint path
     * @param array $body Optional JSON payload for POST/PUT
     * @param array $queryParams Optional query string parameters for GET
     * @return array Parsed JSON response
     * @throws \RuntimeException on error
     */
    private function request(string $method, string $uri, array $body = [], array $queryParams = []): array
    {
        try {
            $token = $this->getValidAccessToken();

            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'x-api-key' => $this->apiKey,
                ]
            ];

            if (!empty($body)) {
                $options['json'] = $body;
            }

            if (!empty($queryParams)) {
                $options['query'] = $queryParams;
            }

            $response = $this->client->request($method, $uri, $options);
            $data = json_decode((string) $response->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON response from API.');
            }

            return $data;
        } catch (RequestException $e) {
            $error = $e->hasResponse() ? (string) $e->getResponse()->getBody() : $e->getMessage();
            throw new \RuntimeException("API request failed: {$error}. URI: {$uri}");
        }
    }

    // ---------- CUSTOMER METHODS ----------

    public function getCustomers(int $page_number = 1, int $page_size = 100): array
    {
        $params = ['page_number' => $page_number, 'page_size' => $page_size];
        return $this->request('GET', 'customers', [], $params);
    }

    public function getCustomer(int $id): array
    {
        return $this->request('GET', "customers/{$id}");
    }

    public function createCustomer(array $payload): array
    {
        return $this->request('POST', 'customers', $payload);
    }

    public function updateCustomer(int $id, array $payload): array
    {
        return $this->request('PUT', "customers/{$id}", $payload);
    }

    public function getProducts(int $page_number = 1, int $page_size = 100): array
    {
        $params = ['page_number' => $page_number, 'page_size' => $page_size];
        return $this->request('GET', 'products', [], $params);
    }

    public function getProduct(int $id): array
    {
        return $this->request('GET', "products/{$id}");
    }

    public function getOrders(int $page_number = 1, int $page_size = 100): array
    {
        $params = ['page_number' => $page_number, 'page_size' => $page_size];
        return $this->request('GET', 'orders', [], $params);
    }

    public function getOrder(int $id): array
    {
        return $this->request('GET', "orders/{$id}");
    }
}
