<?php

namespace App\Command\Job;

use App\Service\ScoreCalculator;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MatchUpFlagsRepository;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:match:scoring',
    description: 'Calcul le score des titres en commun et des matchs associés pour la file d\'attente des matchs à calculer',
)]
class MatchScoringCommand extends Command
{
    public function __construct(
        private ScoreCalculator $scoreCalculator,
        private EntityManagerInterface $_em,
        private MatchUpFlagsRepository $matchUpFlagsRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('nbMatchs', InputArgument::OPTIONAL, 'Number of matchs to update')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('nbMatchs');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
        }

        $stopwatch = new Stopwatch();
        $stopwatch->start('matchScoring');
        $matchsToCalculate = $this->matchUpFlagsRepository->findAllMatchsToCalculate($arg1);
        foreach ($matchsToCalculate as $match) {
            $usersInMatch = $match->getUsersInMatch()->toArray();
            //! FIX Temporaire bug 1 seul user dans le match
            if (count($usersInMatch) > 1) {

                $userA = $usersInMatch[0]->getUser();
                $userB = $usersInMatch[1]->getUser();

                $score = $this->scoreCalculator->commonTracksScoring($match, $userA, $userB);

                if ($score > 0) {
                    $match->setIsActive(true);
                    $match->setUpdatedAtValue();
                    $match->setScore($score);
                    if ($match->getMatchFlags()) {
                        $match->getMatchFlags()->setCalculFlag(false);
                        $match->getMatchFlags()->setUpdatedAtValue();
                    }
                } else {
                    $this->_em->remove($match);
                }
            } else {
                $this->_em->remove($match);
            }
        }

        $this->_em->flush();
        $nbMatchs = count($matchsToCalculate);

        $event = $stopwatch->stop('matchScoring');

        $io->success('Update non scored matchs. ' . $nbMatchs . ' updated matchs. Infos : '. $event);

        return Command::SUCCESS;
    }
}
