<?php

namespace App\Tests\integration;

use App\Tests\DatabaseDependantTestCase;

class FinanceApiClientTest extends DatabaseDependantTestCase
{
    /**
     * @test
     * @group integration
     */
    public function theFinanceApiClientReturnsTheCorrectData()
    {
        // Setup
        $financeApiClient = self::$kernel->getContainer()->get('yahoo-finance-api-client');

        // Do something
        $response = $financeApiClient->fetchStockProfile('AMZN', 'US');

        $stockProfile = json_decode($response->getContent());

        // Make assertions
        $this->assertEquals(200, $response->getStatuscode());
        $this->assertSame('USD', $stockProfile->currency);
        $this->assertSame('NasdaqGS', $stockProfile->exchangeName);
        $this->assertSame('AMZN', $stockProfile->symbol);
        $this->assertSame('Amazon.com, Inc.', $stockProfile->shortName);
        $this->assertSame('US', $stockProfile->region);
        $this->assertIsFloat($stockProfile->price);
        $this->assertIsFloat($stockProfile->previousClose);
    }
}