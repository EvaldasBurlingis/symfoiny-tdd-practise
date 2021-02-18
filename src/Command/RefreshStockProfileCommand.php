<?php

namespace App\Command;

use App\Entity\Stock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshStockProfileCommand extends Command
{
    protected static $defaultName = 'app:refresh-stock-profile';

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Refresh stock profiles by contacting yahoo finance api')
            ->addArgument('symbol', InputArgument::REQUIRED, 'Stock symbol e.g. AMZN for Amazon')
            ->addArgument('region', InputArgument::REQUIRED, 'Stock region e.g. US for United States')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stock = new Stock();
        $stock->setCurrency('USD');
        $stock->setExchangeName('NasdaqGS');
        $stock->setSymbol('AMZN');
        $stock->setShortName('Amazon.com, Inc.');
        $stock->setRegion('US');
        $stock->setPrice(100);
        $stock->setPreviousClose(100);
        $stock->setPriceChange(0);

        $this->entityManager->persist($stock);
        $this->entityManager->flush();
        
        return Command::SUCCESS;
    }
}
