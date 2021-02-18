<?php

namespace App\Tests\feature;

use App\Entity\Stock;
use App\Tests\DatabaseDependantTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class RefreshStockProfileCommandTest extends DatabaseDependantTestCase
{
    /** @test */
    public function theRefreshStockProfileCommandBehavesCorrectlyWhenAStockDoesNotExist()
    {
        // Setup
        $application = new Application(self::$kernel);

        $command = $application->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);
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
    }
}