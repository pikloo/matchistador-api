<?php

namespace App\Service;


use App\Repository\TrackRepository;
use App\Repository\MatchUpRepository;
use App\Service\PlaylistSynchronizer;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserHasTrackRepository;
use App\Repository\UserHasMatchupRepository;

class ScoreCalculator
{

  public function __construct(
    private EntityManagerInterface $entityManager,
    private UserHasTrackRepository $userHasTrackRepository,
    private MatchUpRepository $matchUpRepository,
    private PlaylistSynchronizer $playlistSynchronizer,
    private UserHasMatchupRepository $userHasMatchupRepository,
    private TrackRepository $trackRepository,
    private EntityManagerInterface $_em,
  ) {
  }

  public function commonTracksScoring($match, $user, $userInScope)
  {
    set_time_limit(0);

    $commonTracks = $this->userHasTrackRepository->findCommonsTracksBetweenUsers($user, $userInScope);

    $scoreMatch = 0;


    foreach ($commonTracks as $track) {

      $scoreTrack = $this->addNewScoringMatchTrack($track, $match);
      $scoreMatch += $scoreTrack;
    }

    return $scoreMatch *= 10;
  }




  public function addNewScoringMatchTrack($track, $match)
  {
    //Initialiser le score de la track à 10
    $scoreTrack = 1;
    //Calculer les points de popularités (sur 10)
    $scorePopularity = $this->popularityScoring($track);
    //Calculer les points supertracks (sur 10)
    $scoreSuperTrack = $this->superTrackScoring($track, $match);
    $scoreTrack += $scorePopularity + $scoreSuperTrack;
    // $this->entityManager->flush();

    return $scoreTrack;
  }

  public function matchScoring($match)
  {
    $usersMatch = $match->getUsersInMatch()->toArray();
    $userA = $usersMatch[0]->getUser();
    $userB = $usersMatch[1]->getUser();
    // dd($userA, $userB);
    $commonTracks = $this->userHasTrackRepository->findCommonsTracksBetweenUsers($userA, $userB);
    // dd($commonTracks);
    $scoreMatch = 0;
    foreach ($commonTracks as $track) {
      $scoreTrack = 1;
      $scorePopularity = $this->popularityScoring($track);
      $scoreSuperTrack = $this->superTrackScoring($track, $match);
      $scoreTrack += $scorePopularity + $scoreSuperTrack;
      $scoreMatch += $scoreTrack;
    }
    $scoreMatch *= 10;

    $match->setScore($scoreMatch);
    $match->setUpdatedAt(new \DateTimeImmutable());


    $this->entityManager->flush($match);
    return $scoreMatch;
  }

  private function popularityScoring($track)
  {
    return ($track->getPopularity() !== null)
      ? 1 - ($track->getPopularity() / 100)
      : 1 - (10 / 100);
  }

  private function superTrackScoring($track, $match)
  {

    $usersMatch = $this->userHasMatchupRepository->findByMatch($match);
    // dd($usersMatch);

    $usersTracks = [];
    foreach ($usersMatch as $userMatch) {
      $userTrack = $this->userHasTrackRepository->findOneBy(
        ['user' => $userMatch->getUser(), 'track' => $track]
      );
      $usersTracks[] = $userTrack;
    }
    $scoreSuperTrack = 0;
    if ($usersTracks[0] !== null && $usersTracks[1] !== null) {
      $isUserSuperTrack = $usersTracks[0]->getIsSuperTrack();

      $isUserInverseSuperTrack = $usersTracks[1]->getIsSuperTrack();

      if ($isUserSuperTrack) $scoreSuperTrack += 0.5;
      if ($isUserInverseSuperTrack) $scoreSuperTrack += 0.5;
    };


    return $scoreSuperTrack;
  }
}
