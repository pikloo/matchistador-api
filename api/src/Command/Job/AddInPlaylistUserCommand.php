<?php

namespace App\Command\Job;

use App\Service\MatchGenerator;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserTrackFlagsRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:add:playlist',
    description: 'Cherche des matchs à recalculer et des nouveaux match à partir des ajouts de tracks dans les playlists',
)]
class AddInPlaylistUserCommand extends Command
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

        $createUserTracksFlags = $this->userTrackFlagsRepository->findAllCreateUTF(500);
        $nbMatchToRecalculate = 0;

        foreach ($createUserTracksFlags as $userTrackFlag) {
            $matchs = $this->userTrackFlagsRepository->findMatchByCommonTrack($userTrackFlag);
            $user = $userTrackFlag->getUserTrack()->getUser();

            foreach ($matchs as $match) {

                if ($match->getMatchFlags()) $match->getMatchFlags()->setCalculFlag(true);
                $match->setUpdatedAtValue();
                $nbMatchToRecalculate++;
            }

            $userTrackFlag->setCreateFlag(false);
            $userTrackFlag->setUpdatedAtValue();
            $this->_em->flush();

            $usersInScope = $this->matchGenerator->getUsersInScope($user->getUserData(), $user->getUserData()->getLocation());
            $this->matchGenerator->getNewMatchs($usersInScope, $user, 50);
        }

        $io->success('Update rescoring matchs. ' . $nbMatchToRecalculate . ' matchs to recalculate');

        return Command::SUCCESS;
    }
}
