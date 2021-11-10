<?php

namespace App\Command\Job;

use App\Service\MatchGenerator;
use App\Service\ScoreCalculator;
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
  name: 'app:delete:playlist',
  description: 'Cherche des matchs tracks à supprimer et les matchs à recalculer dans les playlists',
)]
class DeleteInPlaylistUserCommand extends Command
{
  public function __construct(
    private MatchGenerator $matchGenerator,
    private ScoreCalculator $scoreCalculator,
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
    $stopwatch->start('delete tracks');

    $deleteUserTracksFlags = $this->userTrackFlagsRepository->findAllDeleteUTF(100);
    $nbCommonsTrackToReScoring = 0;

    foreach ($deleteUserTracksFlags as $userTrackFlag) {
      //Cherche les matchs qui ont la track en commun
      $matchs = $this->userTrackFlagsRepository->findMatchByCommonTrack($userTrackFlag);

      if ($userTrackFlag->getUserTrack()) {
        $this->_em->remove($userTrackFlag->getUserTrack());
        $this->_em->flush();
      }

      //Recalcul des matchs 
      foreach ($matchs as $match) {
        $score = $this->scoreCalculator->matchScoring($match);
        $nbCommonsTrackToReScoring++;
        if ($score === 0) {
          $this->_em->remove($match);
          $this->_em->flush();
        }
      }

    }

    $event = $stopwatch->stop('delete tracks');

    $io->success(
      'Update delete tracks in userplaylist. ' . $nbCommonsTrackToReScoring . ' commonsTracks to rescoring
    Infos : ' . $event
    );

    return Command::SUCCESS;
  }
}
