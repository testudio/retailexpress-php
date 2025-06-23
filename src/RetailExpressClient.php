<?php
// src/RetailExpressClient.php

namespace RetailExpress;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Retail Express API PHP Client
 *
 * Supports API v2.1
 * @link https://developer.retailexpress.com.au
 */
class RetailExpressClient
{
    private string $apiKey;
    private string $baseUrl;
    private Client $client;

    /**
     * Constructor
     *
     * @param string $apiKey Your Retail Express API key
     * @param string $version API version (default: v2.1)
     * @param string $base Base URL (default: Retail Express API URL)
     */
    public function __construct(string $apiKey, string $version = 'v2.1', string $base = 'https://api.retailexpress.com.au')
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($base, '/') . '/' . $version;
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 30,
            'headers'  => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ],
        ]);
    }

    /**
     * Generic HTTP request wrapper
     *
     * @param string $method GET|POST|PUT|DELETE
     * @param string $uri Relative endpoint path
     * @param array $body Optional JSON payload
     * @return array Parsed JSON response
     * @throws \RuntimeException on error
     */
    private function request(string $method, string $uri, array $body = []): array
    {
        try {
            $options = [];
            if (!empty($body)) {
                $options['json'] = $body;
            }

            $response = $this->client->request($method, "$uri.json", $options);
            $data = json_decode($response->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON response');
            }

            return $data;
        } catch (RequestException $e) {
            $error = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            throw new \RuntimeException("API request failed: {$error}");
        }
    }

    // ---------- CUSTOMER METHODS ----------

    public function getCustomers(int $page = 1, int $perPage = 100): array
    {
        return $this->request('GET', "customers?per_page={$perPage}&page={$page}");
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

    // ---------- PRODUCT METHODS ----------

    public function getProducts(int $page = 1, int $perPage = 100): array
    {
        return $this->request('GET', "products?per_page={$perPage}&page={$page}");
    }

    public function getProduct(int $id): array
    {
        return $this->request('GET', "products/{$id}");
    }

    // ---------- ORDER METHODS ----------

    public function getOrders(int $page = 1, int $perPage = 100): array
    {
        return $this->request('GET', "orders?per_page={$perPage}&page={$page}");
    }

    public function getOrder(int $id): array
    {
        return $this->request('GET', "orders/{$id}");
    }
}
