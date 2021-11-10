<?php

namespace App\Command\Job;

use App\Service\MatchGenerator;
use App\Repository\UserDataRepository;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:activation:users',
    description: 'Active les utilisateurs du générateur (token: 111111)',
)]
class ActivationGeneratorCommand extends Command
{
    
  CONST USER_TOKEN = '111111';

  public function __construct(
        private UserDataRepository $userDataRepository,
        private MatchGenerator $matchGenerator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('nbUser', InputArgument::OPTIONAL, 'Number of users to activate')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('nbUser');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
        }

        $stopwatch = new Stopwatch();
        $stopwatch->start('activation');

        $userDatas = $this->userDataRepository->findUsersDatasByTokenGenerator(self::USER_TOKEN, $arg1);
        $nbUserActivate = 0;
        foreach ($userDatas as $userData) {
          $userData->setActivationToken(null);
          $user = $userData->getUser();
          $user->setIsActive(true);
          $this->matchGenerator->usersFinder($user, $userData, 30);
          $nbUserActivate++;
        }

        $event = $stopwatch->stop('activation');

        $io->success('Users Activation. '. $nbUserActivate . ' users activated . Infos : '. $event);

        return Command::SUCCESS;
    }
}