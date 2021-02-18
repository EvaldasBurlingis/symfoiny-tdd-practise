<?php

namespace App\Tests;

use App\Entity\Stock;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StockTest extends KernelTestCase
{

    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        DatabasePrimer::prime($kernel);
        
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = 0;
    }

    /** @test */
    public function aStockRecordCanBeCreatedInDatabase()
    {
        // set up
        $stockDummyData = [
            'symbol'      => 'AMZN',
            'shortName'   => 'Amazon Inc',
            'currency'    => 'USD',
            'exchangeName' => 'Nasdaq',
            'region'      => 'US'
        ];

        $stock = new Stock();

        $stock->setSymbol($stockDummyData['symbol']);
        $stock->setShortName($stockDummyData['shortName']);
        $stock->setCurrency($stockDummyData['currency']);
        $stock->setExchangeName($stockDummyData['exchangeName']);
        $stock->setRegion($stockDummyData['region']);

        $price = 1000;
        $previousClose = 1100;
        $priceChange = $price - $previousClose;

        $stock->setPrice($price);
        $stock->setPreviousClose($previousClose);
        $stock->setPriceChange($priceChange);

        $this->entityManager->persist($stock);

        // do something
        $this->entityManager->flush();

        $stockRepository = $this->entityManager->getRepository(Stock::class);

        $stockRecord = $stockRepository->findOneBy(['symbol' => 'AMZN']);

        // make assertions
        $this->assertEquals($stockDummyData['symbol'], $stockRecord->getSymbol());
        $this->assertEquals($stockDummyData['shortName'], $stockRecord->getShortName());
        $this->assertEquals($stockDummyData['currency'], $stockRecord->getCurrency());
        $this->assertEquals($stockDummyData['exchangeName'], $stockRecord->getExchangeName());
        $this->assertEquals($stockDummyData['region'], $stockRecord->getRegion());
        $this->assertEquals($price, $stockRecord->getPrice());
        $this->assertEquals($previousClose, $stockRecord->getPreviousClose());
        $this->assertEquals($priceChange, $stockRecord->getPriceChange());
    }
}