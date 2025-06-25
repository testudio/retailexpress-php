# RetailExpress PHP Client

A PHP client wrapper for the Retail Express API (v2.1), built with Guzzle.

## Installation

```bash
composer require testudio/retailexpress-php
```

## Usage

```php
use RetailExpress\RetailExpressClient;

$client = new RetailExpressClient('your_api_key');

// Fetch customers
$customers = $client->getCustomers();

// Create a customer
$newCustomer = $client->createCustomer([
    'first_name' => 'John',
    'last_name' => 'Smith',
    'email' => 'john@example.com'
]);

// Fetch products
$products = $client->getProducts();

// Get a specific order
$order = $client->getOrder(123);
```

## Endpoints Covered

- Customers: list, get, create, update
- Products: list, get
- Product Detail Logs: list, get
- Product Prices: list, get
- Orders: list, get

## Requirements

- PHP 8.0+
- Guzzle 7+

## Testing

```bash
composer install
vendor/bin/phpunit
```
