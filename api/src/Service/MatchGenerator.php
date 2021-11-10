<?php

namespace App\Service;

use App\Entity\MatchUp;
use App\Entity\MatchUpFlags;
use App\Entity\UserHasMatchup;
use App\Entity\UserMatchUpFlags;
use App\Service\ScoreCalculator;

use App\Repository\UserRepository;
use App\Repository\MatchUpRepository;
use App\Repository\UserDataRepository;
use function PHPUnit\Framework\isType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserHasTrackRepository;
use App\Repository\UserHasMatchupRepository;

class MatchGenerator
{
  const LIMIT_NB_MATCHS = 30;
  private $userLimit = false;

  public function __construct(
    private EntityManagerInterface $entityManager,
    private UserDataRepository $userDataRepository,
    private MatchUpRepository $matchUpRepository,
    private ScoreCalculator $scoreCalculator,
    private UserHasMatchupRepository $userHasMatchupRepository,
    private UserHasTrackRepository $userHasTrackRepository,
    private UserRepository $userRepository,
  ) {
  }

  public function usersFinder($user, $userData, $limit = null)
  {
    $previousUserMatchs = $this->userHasMatchupRepository->findInverseMatch($user);
    $usersInScope = $this->getUsersInScope($userData, $userData->getLocation());
    if (!empty($previousUserMatchs)) $this->deleteDeprecatedMatchs($previousUserMatchs, array_map(fn ($array) => $array["user"], $usersInScope));
    $newMatchs = $this->getNewMatchs($usersInScope, $user, $limit);

    $this->entityManager->flush();

    return array_merge($previousUserMatchs, $newMatchs);
  }

  public function deleteMatch($match)
  {
    $this->entityManager->remove($match);
    $this->entityManager->flush();
  }

  public function getUsersInScope($userData, $location)
  {
    $orientationToSearch = [
      $userData->getGender(),
      $userData->getSexualOrientation()
    ];

    $usersInScope = $this->userRepository->findUsersInScope($location, $orientationToSearch);

    $userInScopeWithCommonTrack = [];
    foreach ($usersInScope as $userInScope) {
      $isCommonTrack = $this->checkCommonTrack($userData->getUser(), $userInScope['user']);
      if ($isCommonTrack) $userInScopeWithCommonTrack[] = $userInScope;
    }

    // if (count($userInScope['user']->getUserMatchs()) === SELF::LIMIT_NB_MATCHS) $this->setUserLimit(true);

    return $userInScopeWithCommonTrack;
  }

  private function deleteDeprecatedMatchs($matchs, $newUsers)
  {
    // Si un user présent dans les matchs de userA n'est pas présent dans la liste newUsers
    // ==> il n'y a plus match donc à supprimer
    $matchsToRemove = [];
    foreach ($matchs as  $previousMatch) {
      if (
        !in_array($previousMatch->getUser(), $newUsers)
      ) {
        $matchsToRemove[] = $previousMatch;
        $this->entityManager->remove($previousMatch);
      }
    }
    // dd($matchsToRemove, $newUsers);
    return $matchsToRemove;
  }

  public function getNewMatchs($usersInScope, $user, $limit = null)
  {

    $matchsToCreate = [];
    //TODO: à optimiser SQL
    $newUserMatch = [];
    foreach ($usersInScope as $userInScope) {
      $isMatchsExist = $this->userHasMatchupRepository->findMatchByUsers($user, $userInScope['user']);
      if (!count($isMatchsExist) > 0) $newUserMatch[] = $userInScope;
    }


    foreach ($newUserMatch as $userInScope) {

      //On crée un match et on le flush
      $match = new MatchUp();
      $match->setDistance($userInScope['distance']);

      $this->entityManager->persist($match);
      $this->entityManager->flush();
      $matchsToCreate[] = $match;

      //On crée les user_match
      $userMatch = new UserHasMatchup();
      $userMatch->setUser($user);
      $userMatch->setMatch($match);
      $userMatch->setCreatedAtValue();
      $this->entityManager->persist($userMatch);
      $this->entityManager->flush();

      $userMatchInverse = new UserHasMatchup();
      $userMatchInverse->setUser($userInScope['user']);
      $userMatchInverse->setMatch($match);
      $userMatchInverse->setCreatedAtValue();
      $this->entityManager->persist($userMatchInverse);
      $this->entityManager->flush();

      //On crée le flag match associé
      $matchFlags = new MatchUpFlags();
      $matchFlags->setMatch($match);
      $matchFlags->setCreatedAtValue();
      $this->entityManager->persist($matchFlags);
      $this->entityManager->flush();

      if ($this->getUserLimit()) {
        $matchFlags->setCalculFlag(true);
        $matchFlags->setUpdatedAtValue();
        break;
      } else {
        $score = $this->scoreCalculator->commonTracksScoring($match, $user, $userInScope['user']);
        $match->setIsActive(true);
        $match->setUpdatedAtValue();
        $match->setScore($score);
      }

      $this->entityManager->flush();
      if (count($matchsToCreate) === $limit && $limit !== null) $this->setUserLimit(true);
    }
    // dd(count($matchsToCreate));
    return $matchsToCreate;
  }

  private function checkCommonTrack($user, $userMatch)
  {
    //TODO tester sous requete à la place de COUNT * > 1
    $commonTracks = $this->userHasTrackRepository->findCommonsTracksBetweenUsers($user, $userMatch, 1);

    return count($commonTracks) > 0 ? true : false;
  }

  /**
   * Get the value of userLimit
   */
  public function getUserLimit()
  {
    return $this->userLimit;
  }

  /**
   * Set the value of userLimit
   *
   * @return  self
   */
  public function setUserLimit($userLimit)
  {
    $this->userLimit = $userLimit;

    return $this;
  }
}
