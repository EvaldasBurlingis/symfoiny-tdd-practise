<?php

namespace App\Tests;

use App\Entity\Stock;
use App\Tests\DatabaseDependantTestCase;

class StockTest extends DatabaseDependantTestCase
{
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