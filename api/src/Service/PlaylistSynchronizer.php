<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Track;
use App\Entity\UserHasTrack;
use App\Entity\UserTrackFlags;
use App\Repository\UserRepository;
use App\Repository\TrackRepository;
use App\Repository\MatchUpRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserHasTrackRepository;
use App\Repository\UserHasMatchupRepository;
use App\Repository\UserTrackFlagsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class PlaylistSynchronizer
{
  const EMPTY_DATAS_MESSAGE = "Aucune donnée envoyée";

  public function __construct(
    private TrackRepository $trackRepository,
    private EntityManagerInterface $_em,
    private SerializerInterface $serializer,
    private ValidatorInterface $validator,
    private UserHasTrackRepository $userHasTrackRepository,
    private MatchUpRepository $matchUpRepository,
    private UserHasMatchupRepository $userHasMatchupRepository,
    private UserTrackFlagsRepository $userTrackFlagsRepository,
    private UserRepository $userRepository,
  ) {
  }

  /**
   * Récupère et retourne le User à partir de l'id envoyé
   * 
   *
   * @param string $user
   * @return User
   */
  public function getUser($user)
  {
    $user = $this->userRepository->find($user);

    return $user;
  }

  /**
   * Récupère et retourne les titres actifs dans la playlist du user
   *
   * @param User $user
   * @return array
   */
  public function getActivedUserTracks(User $user)
  {
    return $this->userHasTrackRepository->findBy(
      [
        'user' => $user,
        'isActive' => true,
      ]
    );
  }

  /**
   * Désérialize un tableaux de titres en objet Track
   * et retourne un tableau multidimensionnel contenant
   * les objets tracks, les erreurs de validations de ces objets
   * et le statut "isSuperTrack"
   *
   * @param array $tracks
   * @return array
   */
  public function trackJsonInitializer($tracks)
  {
    if ($tracks === []) return new JsonResponse(["error" => self::EMPTY_DATAS_MESSAGE], Response::HTTP_BAD_REQUEST);
    $normalizedTracks = [];
    foreach ($tracks as $track) {
      $trackNormalizer = $this->trackNormalizer($track);
      $normalizedTracks[] =
        [
          'track' =>  $trackNormalizer,
          // 'errors' =>  !empty($trackNormalizer['errors']) ? $trackNormalizer['errors'] : null,
          'isSuperTrack' => ($track['isTopTrack']) ?  true : false
        ];
    }

    return $normalizedTracks;
  }


  /**
   * Récupère et retourne les titres à ajouter à la base,
   * les titres à ajouter à la playlist du user et les erreurs
   *
   * @param array $tracks
   * @return array
   */
  public function getTracksToCreateFromData($tracks)
  {
    $tracksToCreate = [];
    $tracksToAddToUserPlaylist = [];
    $errorsList = [];
    //TODO: gérer le select en masse via l'ORM

    foreach ($tracks as $track) {
      $existedTrack = $this->getExistedTrack($track['track']);

      if (null !== $existedTrack) {
        $tracksToAddToUserPlaylist[] = [
          'track' => $existedTrack,
          'isSuperTrack' => $track['isSuperTrack']
        ];

        $this->_em->refresh($existedTrack);
        if ($track['track']->getPopularity() !== $existedTrack->getPopularity()) {
          $existedTrack->setPopularity($track['track']->getPopularity());
          $this->_em->flush();
        }

        if ($track['track']->getPictureUrl() !== $existedTrack->getPictureUrl()) {
          $existedTrack->setPictureUrl($track['track']->getPictureUrl());
          $this->_em->flush();
        }
        if ($track['track']->getSpotifyPreviewUrl() !== $existedTrack->getSpotifyPreviewUrl()) {
          $existedTrack->setSpotifyPreviewUrl($track['track']->getSpotifyPreviewUrl());
          $this->_em->flush();
        }
        if ($track['track']->getDeezerPreviewUrl() !== $existedTrack->getDeezerPreviewUrl()) {
          $existedTrack->setDeezerPreviewUrl($track['track']->getDeezerPreviewUrl());
          $this->_em->flush();
        }
      } else {
        $tracksToCreate[] = $track;
      }
      // foreach ($track['errors'] as $error) {
      //   $errorsList[$track['track']->getName()][$error->getPropertyPath()] = $error->getMessage();
      // }
    }
    // dd($tracksToAddToUserPlaylist);
    return [
      'tracksToCreate' => $tracksToCreate,
      'tracksToAddToUserPlaylist' => $tracksToAddToUserPlaylist,
      // 'errors' => $errorsList,
    ];
  }


  /**
   * Sauvegarde les titres à ajouter et les titres à ajouter
   * à la playlist du User
   *
   * @param User $user
   * @param array $tracksToCreate
   * @param array $tracksToAddToUserPlaylist
   * @return array
   */
  public function saveTracks(User $user, $tracksToCreate, $tracksToAddToUserPlaylist, $context = null)
  {
    $saveUserTracks = [];
    if (!empty($tracksToCreate)) {
      foreach ($tracksToCreate as $trackToCreate) {
        $track = $trackToCreate['track'];
        $this->_em->persist($track);
        $this->_em->flush();
        $saveUserTracks[] = $this->addTrackInUserPlaylist($user, $track, $trackToCreate['isSuperTrack']);
      }
    }
    if (!empty($tracksToAddToUserPlaylist)) {
      foreach ($tracksToAddToUserPlaylist as $trackToAddToUserPlaylist) {
        $saveUserTracks[] = $this->addTrackInUserPlaylist($user, $trackToAddToUserPlaylist['track'], $trackToAddToUserPlaylist['isSuperTrack'], 'update');
      }
    }

    return $saveUserTracks;
  }


  /**
   * Récupère les titres à supprimer de la playlist User
   * Désactive ces titres et leur place un flag pour les supprimer définitivement
   *
   * @param array $tracks
   * @param User $user
   * @return array
   */
  public function setUserTracksToDelete($tracks, User $user)
  {
    set_time_limit(0);

    $tracksToDelete2 = $this->userHasTrackRepository->findTracksToDeleteByUser($tracks, $user);
    $userTracks = $this->userHasTrackRepository->findTrackByUser($user);

    // // dd($tracks);
    // $trackToDelete3 = []; 
    // foreach($userTracks as $userTrack){
    //   if (!in_array($userTrack, $tracks)){
    //     $trackToDelete3[] = $userTrack;
    //   }
    // }

    // $tracksToDelete = array_udiff(
    //   $userTracks,
    //   $tracks,
    //   function ($a, $b) {
    //     return strcmp(spl_object_hash($a), spl_object_hash($b));
    //   }
    // );
    // dd($tracks, $userTracks, $tracksToDelete2
    // , array_udiff(
    //   $userTracks,
    //   $tracks,
    //   function ($a, $b) {
    //     return spl_object_hash($a->getId()->getUuid()) -  $b->getId()->toString();
    //   }
    // )
    // );

    // dump($tracks, $userTracks, $tracksToDelete, $tracksToDelete2,$trackToDelete3);
    if (count($tracksToDelete2) > 0) {
      foreach ($tracksToDelete2 as $track) {
        // $track = $this->getTrackInUserPlaylist($track, $user);
        $track->setIsActive(false);
        $userTrackFlags = $this->userTrackFlagsRepository->findOneByUserTrack($track);
        $userTrackFlags->setDeleteFlag(true);
        if ($userTrackFlags->getCreateFlag()) $userTrackFlags->setCreateFlag(false);
        if ($userTrackFlags->getUpdateFlag()) $userTrackFlags->setUpdateFlag(false);
        $this->_em->flush($track);
        $this->_em->flush($userTrackFlags);
      }
    }

    return $tracksToDelete2;
  }

  /**
   * Sérialize un array de data contenant un titre, désérialize en objet Track
   * et vérifie si l'objet est valide puis retourne l'instance de Track et les erreurs
   *
   * @param array $track
   * @return array
   */
  private function trackNormalizer($track)
  {
    $newTrack = new Track;
    $track = $this->serializer->serialize($track, 'json');
    $track = $this->serializer->deserialize($track, Track::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $newTrack]);
    // $errors = $this->validator->validate($track);
    // return [
    //   "track" => $track,
    //   "errors" => $errors,
    // ];

    return $track;
  }

  /**
   * Cherche si un titre existe déja dans le DB
   * retourne une instance d'objet Track ou null
   *
   * @param [type] $track
   * @return Track|null
   */
  private function getExistedTrack($track): Track|null
  {
    $existedTrack = $this->trackRepository->findOneBy(
      [
        'name' => $track->getName(),
        'artist' => $track->getArtist()
      ]
    );

    return $existedTrack;
  }


  /**
   * Crée un nouvel objet U.T (UserHasTrack), vérifie s'il existe déja ou pas
   * Si le U.T n'existe pas, l'enrgistrer et créer un flag de création pour ce U.T
   * Sinon changer les flags et l'activation du U.T en fonction des données envoyées
   * Retourne l'objet Track ajouté
   * TODO: retourner aussi l'objet si modification
   *
   * @param User $user
   * @param Track $track
   * @param bool $isSuperTrack
   * @return void
   */
  private function addTrackInUserPlaylist(User $user, Track $track, bool $isSuperTrack, $context = null)
  {
    $userTrack = $this->getTrackInUserPlaylist($track, $user);
    if ($userTrack !== null) {

      //Si la track est suppr de la playlist et remise avant le traitement des batchs on annule le statut de suppression
      if (!$userTrack->getIsActive() || $userTrack->getIsSuperTrack() !== $isSuperTrack) {
        $existedUserTrackFlags = $this->userTrackFlagsRepository->findOneByUserTrack($userTrack);
      }

      if (!$userTrack->getIsActive() && $existedUserTrackFlags->getDeleteFlag()) {
        $userTrack->setIsActive(true);
        $existedUserTrackFlags->setDeleteFlag(false);
        $this->_em->flush();
      }
      //Si le statut supertrack change et passer le flag sur update pour les batchs
      if ($userTrack->getIsSuperTrack() !== $isSuperTrack) {
        $existedUserTrackFlags = $this->userTrackFlagsRepository->findOneByUserTrack($userTrack);
        $userTrack->setIsSuperTrack($isSuperTrack);
        if (!$existedUserTrackFlags->getCreateFlag()) $existedUserTrackFlags->setUpdateFlag(true);
        $this->_em->flush();
      }
      //Passer le statut de la UT en actif
      if ($context !== 'update') {
        $userTrack->setIsActive(true);
        $userTrack->setUpdatedAtValue();
        $this->_em->flush();
      }
    } else {
      $userTrack = new UserHasTrack;
      $userTrack->setIsSuperTrack($isSuperTrack);
      $userTrack->setUser($user);
      $userTrack->setTrack($track);
      $userTrack->setIsActive(true);
      $this->_em->persist($userTrack);
      $userTrackFlags = new UserTrackFlags;
      $userTrackFlags->setUserTrack($userTrack);
      $userTrackFlags->setCreateFlag(true);
      $userTrackFlags->setCreatedAtValue();
      $this->_em->persist($userTrackFlags);
      $this->_em->flush();
    }

    // $this->_em->flush();

    return $userTrack;
  }


  /**
   * Cherche un titre dans la playlist du User et le retourne
   *
   * @param Track $track
   * @param User $user
   * @return UserHasTrack|null
   */
  private function getTrackInUserPlaylist(Track $track, User $user): UserHasTrack|null
  {
    $userTrack = $this->userHasTrackRepository->findOneBy(
      [
        'track' => $track,
        'user' => $user
      ]
    );

    return $userTrack;
  }


  public function removeUserTracks($userTracks)
  {
    foreach ($userTracks as $userTrack) {
      $this->_em->remove($userTrack);
      $this->_em->flush();
    }
  }
}
