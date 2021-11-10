<?php

namespace App\Command\Job;

use App\Service\MatchGenerator;
use App\Repository\MatchUpRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
  name: 'app:delete:match',
  description: 'Supprime les matchs actifs au score nul',
)]
class DeleteMatchCommand extends Command
{
  public function __construct(
    private MatchGenerator $matchGenerator,
    private EntityManagerInterface $_em,
    private MatchUpRepository $matchUpRepository,
  ) {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this
      ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
      ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $arg1 = $input->getArgument('arg1');

    if ($arg1) {
      $io->note(sprintf('You passed an argument: %s', $arg1));
    }

    if ($input->getOption('option1')) {
    }

    $matchsToDelete = $this->matchUpRepository->findMatchToDelete(1000);
    $nbMatchsToDelete = 0;

    foreach ($matchsToDelete as $match) {
      $this->_em->remove($match);
      $this->_em->flush();
      $nbMatchsToDelete++;
    }

    $io->success('Delete matchs. ' . $nbMatchsToDelete . ' matchs to delete');

    return Command::SUCCESS;
  }
}
