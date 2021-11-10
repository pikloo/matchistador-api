<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Track;
use App\Entity\UserData;
use App\Entity\UserHasTrack;
use App\Entity\UserTrackFlags;
use App\Service\MatchGenerator;
use App\Repository\TrackRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture
{
  const NB_USERS = 500;
  const NB_TRACKS = 1000;
  const FEMALE = "female";
  const MALE = "male";
  const BOTH = "both";
  const GENDER = [
    self::FEMALE,
    self::MALE,
  ];
  const ORIENTATION = [
    self::FEMALE,
    self::MALE,
    self::BOTH,
  ];
  const STREAMING_PLATFORM = [];

  public function __construct(
    private UserPasswordHasherInterface $passwordHasher,
    private MatchGenerator $matchGenerator,
    private TrackRepository $trackRepository,
  ) {
  }

  public function load(ObjectManager $manager)
  {
    $faker = Factory::create('fr_FR');
    $faker->seed(5678);

    //! A ajouter en cas de recréation de track
    // $tracks = [];
    // for ($i = 0; $i < SELF::NB_TRACKS; $i++) {
    //   $track = new Track();
    //   $track->setName('title ' . $i);
    //   $track->setArtist('artist');
    //   $track->setAlbum('album');
    //   $track->setPopularity($faker->numberBetween(1, 100));
    //   $manager->persist($track);
    //   $tracks[] = $track;
    // }
    //! A supprimer en cas de recréation de track
    $tracks= $this->trackRepository->findAll();

    // for ($i = 0; $i < SELF::NB_USERS; $i++) {
    //   $user = new User();
    //   $user->setEmail($faker->unique()->email());
    //   $user->setPassword($this->passwordHasher->hashPassword($user, $faker->regexify('^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$')));
    //   $user->setRoles(["ROLE_USER"]);

    //   //userTracks
    //   shuffle($tracks);
    //   for ($r = 0; $r < mt_rand(1, 1000); $r++) {
    //     $userTrack = new UserHasTrack();
    //     $randomTrack = $tracks[$r];
    //     $userTrack->setUser($user);
    //     $userTrack->setTrack($randomTrack);
    //     $userTrack->setIsSuperTrack((bool)random_int(0, 1));
    //     $userTrack->setIsActive(true);
    //     $manager->persist($userTrack);

    //     $userTrackFlags = new UserTrackFlags;
    //     $userTrackFlags->setUserTrack($userTrack);
    //     $userTrackFlags->setCreateFlag(true);
    //     $manager->persist($userTrackFlags);
    //   }

    //   $manager->persist($user);

    //   $userData = new UserData();
    //   $userData->setName($faker->lastName());
    //   $userData->setPositionLat($faker->randomFloat(5, 48, 49));
    //   $userData->setPositionLng($faker->randomFloat(5, 2, 3));
    //   $userData->setBirthDate($faker->dateTimeBetween('-60 years', '-18 years'));
    //   $userData->setGender(self::GENDER[array_rand(self::GENDER, 1)]);
    //   $userData->setSexualOrientation(self::ORIENTATION[array_rand(self::ORIENTATION, 1)]);

    //   $location = sprintf('POINT(%f %f)',  $userData->getPositionLng(), $userData->getPositionLat());
    //   $userData->setLocation($location);
    //   // $this->matchGenerator->usersFinder($user, $userData);

    //   $userData->setUser($user);
    //   $manager->persist($userData);
    // }

    // $pikloo = new User();
    // $pikloo->setEmail('pikloo@pikloo.fr');
    // $pikloo->setPassword($this->passwordHasher->hashPassword($pikloo, 'Pikloo123'));
    // $pikloo->setRoles(["ROLE_USER", "ROLE_ADMIN"]);


    // //userTracks
    // shuffle($tracks);
    // for ($r = 0; $r < 750; $r++) {
    //   $piklooTrack = new UserHasTrack();
    //   $randomTrack = $tracks[$r];
    //   $piklooTrack->setUser($pikloo);
    //   $piklooTrack->setTrack($randomTrack);
    //   $piklooTrack->setIsSuperTrack((bool)random_int(0, 1));
    //   $piklooTrack->setIsActive(true);
    //   $manager->persist($piklooTrack);

    //   $userTrackFlags = new UserTrackFlags;
    //     $userTrackFlags->setUserTrack($piklooTrack);
    //     $userTrackFlags->setCreateFlag(true);
    //     $manager->persist($userTrackFlags);
    // }

    // $manager->persist($pikloo);

    // $userPiklooData = new UserData();
    // $userPiklooData->setName('Pikloo');
    // $userPiklooData->setPositionLat(48.86103038041532);
    // $userPiklooData->setPositionLng(2.4050238828193455);
    // $userPiklooData->setBirthDate($faker->dateTimeBetween('-60 years', '-18 years'));
    // $userPiklooData->setGender(self::MALE);
    // $userPiklooData->setSexualOrientation(self::FEMALE);
    // $location = sprintf('POINT(%f %f)',  $userPiklooData->getPositionLng(), $userPiklooData->getPositionLat());
    // $userPiklooData->setLocation($location);
    // $userPiklooData->setUser($pikloo);
    // // $this->matchGenerator->usersFinder($pikloo, $userPiklooData);


    // $manager->persist($userPiklooData);

    $pikloo2 = new User();
    $pikloo2->setEmail('pikloo13@pikloo.fr');
    $pikloo2->setPassword($this->passwordHasher->hashPassword($pikloo2, 'Pikloo123'));
    $pikloo2->setRoles(["ROLE_USER", "ROLE_ADMIN"]);

    //userTracks
    $tracks= $this->trackRepository->findAll();
    shuffle($tracks);
    for ($r = 0; $r < 800; $r++) {
      $pikloo2Track = new UserHasTrack();
      $randomTrack = $tracks[$r];
      $pikloo2Track->setUser($pikloo2);
      $pikloo2Track->setTrack($randomTrack);
      $pikloo2Track->setIsSuperTrack((bool)random_int(0, 1));
      $pikloo2Track->setIsActive(true);
      $manager->persist($pikloo2Track);

      $userTrackFlags = new UserTrackFlags;
        $userTrackFlags->setUserTrack($pikloo2Track);
        $userTrackFlags->setCreateFlag(true);
        $manager->persist($userTrackFlags);
    }

    $manager->persist($pikloo2);

    $userPiklooData2 = new UserData();
    $userPiklooData2->setName('Pikloo13');
    $userPiklooData2->setPositionLat(4.86103038041532);
    $userPiklooData2->setPositionLng(2.150238828193455);
    $userPiklooData2->setBirthDate($faker->dateTimeBetween('-60 years', '-18 years'));
    $userPiklooData2->setGender(self::FEMALE);
    $userPiklooData2->setSexualOrientation(self::BOTH);
    $location = sprintf('POINT(%f %f)',  $userPiklooData2->getPositionLng(), $userPiklooData2->getPositionLat());
    $userPiklooData2->setLocation($location);
    $userPiklooData2->setUser($pikloo2);
    $manager->persist($userPiklooData2);
    $this->matchGenerator->usersFinder($pikloo2, $userPiklooData2);

    $manager->flush();
  }
  
}
