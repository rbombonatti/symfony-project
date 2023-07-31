<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Controller\HashesController;
use App\Repository\HashesRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;


#[AsCommand(
    name: 'generate-hashes',
    description: 'Geração de Hashes via linha de comando. Informe a string de entrada e a quantidade de ocorrências.',
)]
class GenerateHashesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ManagerRegistry $doctrine
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'String de Entrada')
            ->addArgument('arg2', InputArgument::OPTIONAL, 'Quantidade de Hashes a gerar')            
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');
        $arg2 = $input->getArgument('arg2');

        if ($arg1 && $arg2) {
            $hashesRepository = new HashesRepository($this->doctrine);
            $hashes = new HashesController($this->entityManager);
            $hashes->generateHashCascate($arg1, $arg2, $hashesRepository);
            $io->success(sprintf('Foram geradas %s hashes a partir da string %s ', $arg2, $arg1));
            return Command::SUCCESS;
        }

    }
}
