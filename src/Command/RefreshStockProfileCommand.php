<?php

namespace App\Command;

use App\Entity\Stock;
use Doctrine\ORM\EntityManagerInterface;
use App\Http\FinanceApiClientInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class RefreshStockProfileCommand extends Command
{
    protected static $defaultName = 'app:refresh-stock-profile';
    private SerializerInterface $serializer;

    public function __construct(
        private EntityManagerInterface $entityManager, 
        private financeApiClientInterface $financeApiClient)
    {
        parent::__construct();
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
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
        $stockProfile = $this->financeApiClient
                            ->fetchStockProfile($input->getArgument('symbol'), $input->getArgument('region'));

        if ($stockProfile->getStatusCode() !== 200) {
            $output->writeln($stockProfile->getContent());

            return Command::FAILURE;
        }

        $stock = $this->serializer->deserialize($stockProfile->getContent(), Stock::class, 'json');

        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        $output->writeln($stock->getShortName() . ' has been saved/updated.');
        
        return Command::SUCCESS;
    }
}
