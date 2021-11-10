<?php

namespace App\Command\Job;

use App\Service\MatchGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use App\Repository\UserTrackFlagsRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:update:playlist',
    description: 'Cherche des matchs à recalculer dans les playlists',
)]
class UpdateInPlaylistUserCommand extends Command
{
    public function __construct(
        private MatchGenerator $matchGenerator,
        private EntityManagerInterface $_em,
        private UserTrackFlagsRepository $userTrackFlagsRepository,
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

        $stopwatch = new Stopwatch();
        $stopwatch->start('update tracks');

        $updateUserTracksFlags = $this->userTrackFlagsRepository->findAllUpdateUTF(1000);
        $nbCommonsTrackToReScoring = 0;

        foreach ($updateUserTracksFlags as $userTrackFlag) {
            $matchs = $this->userTrackFlagsRepository->findMatchByCommonTrack($userTrackFlag);

            foreach ($matchs as $match) {
                if ($match->getMatchFlags()) $match->getMatchFlags()->setCalculFlag(true);
                $match->setUpdatedAtValue();
                $nbCommonsTrackToReScoring++;
            }

            $userTrackFlag->setUpdateFlag(false);
            $userTrackFlag->setUpdatedAtValue();
            $this->_em->flush();
        }

        $event = $stopwatch->stop('update tracks');

        $io->success(
            'Update update tracks in userplaylist. ' . $nbCommonsTrackToReScoring . ' commonsTracks to rescoring
    Infos : ' . $event
        );

        return Command::SUCCESS;
    }
}
