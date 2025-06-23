<?php
use PHPUnit\Framework\TestCase;
use RetailExpress\RetailExpressClient;

class RetailExpressClientTest extends TestCase
{
    public function testCanInstantiateClient()
    {
        $client = new RetailExpressClient('dummy_key');
        $this->assertInstanceOf(RetailExpressClient::class, $client);
    }
}
