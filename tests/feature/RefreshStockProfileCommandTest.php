<?php

namespace App\Tests\feature;

use App\Entity\Stock;
use App\Http\FakeYahooFinanceApiClient;
use App\Tests\DatabaseDependantTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class RefreshStockProfileCommanddTest extends DatabaseDependantTestCase
{
    /** @test */
    public function theRefreshStockProfileCommandCreatesNewRecordsCorrectly()
    {
        // Setup
        $application = new Application(self::$kernel);

        $command = $application->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        // set up fake return content
        FakeYahooFinanceApiClient::$content = '{"symbol":"AMZN","shortName":"Amazon.com, Inc.","region":"US","exchangeName":"NasdaqGS","currency":"USD",
            "price":3332.32,"previousClose":3308.64,"priceChange":23.68}';

        // Do something
        $commandTester->execute([
            'symbol' => 'AMZN', 
            'region' => 'US'
        ]);

        // Make assertions
        $stockRepository = $this->entityManager->getRepository(Stock::class);

        $stock = $stockRepository->findOneBy(['symbol' => 'AMZN']);

        $this->assertSame('USD', $stock->getCurrency());
        $this->assertSame('NasdaqGS', $stock->getExchangeName());
        $this->assertSame('AMZN', $stock->getSymbol());
        $this->assertSame('Amazon.com, Inc.', $stock->getShortName());
        $this->assertSame('US', $stock->getRegion());
        $this->assertGreaterThan(50, $stock->getPrice());
        $this->assertGreaterThan(50, $stock->getPreviousClose());
        $this->assertStringContainsString('Amazon.com, Inc. has been saved/updated.', $commandTester->getDisplay());
    }

    /** @test */
    public function theRefreshStockProfileCommandUpdatesExistingRecordsCorrectly()
    {
        // Set up
        $stock = new Stock();
        $stock->setSymbol('AMZN');
        $stock->setRegion('US');
        $stock->setExchangeName('NasdaqGS');
        $stock->setCurrency('USD');
        $stock->setPreviousClose(3000);
        $stock->setPrice(3100);
        $stock->setPriceChange(100);
        $stock->setShortName('Amazon.com, Inc.');

        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        $stockId = $stock->getId();

        $application = new Application(self::$kernel);

        $command = $application->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        FakeYahooFinanceApiClient::$statusCode = 200;
        FakeYahooFinanceApiClient::setContent(['previous_close' => 3308.64, 'price' => 3332.32, 'price_change' => 23.68]);

        // Do something
        $commandResponseStatus = $commandTester->execute([
            'symbol' => 'AMZN', 
            'region' => 'US'
        ]);

        // Make assertions
        $stockRepository = $this->entityManager->getRepository(Stock::class);
        $stock = $stockRepository->find($stockId);

        $this->assertEquals(3308.64, $stock->getPreviousClose());
        $this->assertEquals(3332.32, $stock->getPrice());
        $this->assertEquals(23.68, $stock->getPriceChange());
        $stockRecordCount = $stockRepository->createQueryBuilder('stock')
        ->select('count(stock.id)')
        ->getQuery()
        ->getSingleScalarResult();

        // Make assertions
        $this->assertEquals(0, $commandResponseStatus);
        $this->assertEquals(1, $stockRecordCount);
    }

    /** @test */
    public function non200StatusCodeResponsesAreHandledCorrectly()
    {
        // Setup
        $application = new Application(self::$kernel);

        $command = $application->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        FakeYahooFinanceApiClient::$statusCode = 500;
        FakeYahooFinanceApiClient::$content = 'Finance API Client Error';

        // Do something

        $commandResponseStatus = $commandTester->execute([
            'symbol' => 'AMZN', 
            'region' => 'US'
        ]);

        // Make assertions
        $stockRepository = $this->entityManager->getRepository(Stock::class);

        $stockRecordCount = $stockRepository->createQueryBuilder('stock')
                                            ->select('count(stock.id)')
                                            ->getQuery()
                                            ->getSingleScalarResult();

        // Make assertions
        $this->assertEquals(1, $commandResponseStatus);
        $this->assertEquals(0, $stockRecordCount);
        $this->assertStringContainsString('Finance API Client Error', $commandTester->getDisplay());
    }
}