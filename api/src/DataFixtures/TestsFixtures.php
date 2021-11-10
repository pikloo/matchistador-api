<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Track;
use App\Entity\MatchUp;
use App\Entity\UserData;
use App\Entity\UserHasTrack;
use App\Service\MatchGenerator;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestsFixtures extends Fixture
{
    const NB_TRACKS = 10;
    const NB_USERS = 10;

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

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private MatchGenerator $matchGenerator
    ) {
    }

    public function load(ObjectManager $manager)
    {
        // $faker = Factory::create();

        // $tracks = [];
        // for ($i = 1; $i <= SELF::NB_TRACKS; $i++) {
        //     $track = 'track' . $i;
        //     ${$track} = new Track();
        //     ${$track}->setName('title ' . $i);
        //     ${$track}->setArtist('artist');
        //     ${$track}->setAlbum('album');
        //     ${$track}->setPopularity(random_int(1, 100));
        //     $manager->persist(${$track});
        //     $tracks[] = ${$track};
        // }

        // for ($i = 1; $i <= SELF::NB_USERS; $i++) {
        //     $user = 'user' . $i;
        //     ${$user} = new User();
        //     ${$user}->setEmail('user' . $i . '@user' . $i . '.fr');
        //     ${$user}->setPassword($this->passwordHasher->hashPassword(${$user}, 'User1234'));
        //     ${$user}->setRoles(["ROLE_USER"]);
        //     ${$user}->setIsActive(true);

        //     $userData = new UserData();
        //     $userData->setUser(${$user});
        //     $userData->setName('name' . $i);
        //     $userData->setPositionLat($faker->randomFloat(5, 48, 49));
        //     $userData->setPositionLng($faker->randomFloat(5, 2, 3));
        //     $userData->setBirthDate($faker->dateTimeBetween('-60 years', '-18 years'));
        //     $userData->setGender(self::GENDER[array_rand(self::GENDER, 1)]);
        //     $userData->setSexualOrientation(self::ORIENTATION[array_rand(self::ORIENTATION, 1)]);

        //     $location = sprintf('POINT(%f %f)',  $userData->getPositionLng(), $userData->getPositionLat());
        //     $userData->setLocation($location);
        //     // $this->matchGenerator->usersFinder($user, $userData);

        //     $manager->persist($userData);

        //     shuffle($tracks);
        //     for ($r = 0; $r < mt_rand(1, 5); $r++) {
        //         $userTrack = new UserHasTrack();
        //         $randomTrack = $tracks[$r];
        //         $userTrack->setUser(${$user});
        //         $userTrack->setTrack($randomTrack);
        //         $userTrack->setIsSuperTrack((bool)random_int(0, 1));
        //         $manager->persist($userTrack);
        //     }

        //     $manager->flush();

        //     $manager->persist(${$user});
        // }


        // //USERFIX 1 Male => female
        // $pikloo = new User();
        // $pikloo->setEmail('pikloo@pikloo.fr');
        // $pikloo->setPassword($this->passwordHasher->hashPassword($pikloo, 'Pikloo123'));
        // $pikloo->setRoles(["ROLE_USER", "ROLE_ADMIN"]);
        // $pikloo->setIsActive(true);


        // //userTracks
        // shuffle($tracks);
        // for ($r = 0; $r < 10; $r++) {
        //     $piklooTrack = new UserHasTrack();
        //     $randomTrack = $tracks[$r];
        //     $piklooTrack->setUser($pikloo);
        //     $piklooTrack->setTrack($randomTrack);
        //     $piklooTrack->setIsSuperTrack((bool)random_int(0, 1));
        //     $manager->persist($piklooTrack);
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
        // $this->matchGenerator->usersFinder($pikloo, $userPiklooData);

        // $manager->persist($userPiklooData);

        // //USERFIX 2 Female => Male
        // $pikloo2 = new User();
        // $pikloo2->setEmail('pikloo2@pikloo.fr');
        // $pikloo2->setPassword($this->passwordHasher->hashPassword($pikloo2, 'Pikloo123'));
        // $pikloo2->setRoles(["ROLE_USER", "ROLE_ADMIN"]);
        // $pikloo2->setIsActive(true);

        // //userTracks
        // shuffle($tracks);
        // for ($r = 0; $r < 8; $r++) {
        //     $pikloo2Track = new UserHasTrack();
        //     $randomTrack = $tracks[$r];
        //     $pikloo2Track->setUser($pikloo2);
        //     $pikloo2Track->setTrack($randomTrack);
        //     $pikloo2Track->setIsSuperTrack((bool)random_int(0, 1));
        //     $manager->persist($pikloo2Track);
        // }

        // $manager->persist($pikloo2);

        // $userPiklooData2 = new UserData();
        // $userPiklooData2->setName('Pikloo2');
        // $userPiklooData2->setPositionLat(47.86103038041532);
        // $userPiklooData2->setPositionLng(2.050238828193455);
        // $userPiklooData2->setBirthDate($faker->dateTimeBetween('-60 years', '-18 years'));
        // $userPiklooData2->setGender(self::FEMALE);
        // $userPiklooData2->setSexualOrientation(self::MALE);
        // $location = sprintf('POINT(%f %f)',  $userPiklooData2->getPositionLng(), $userPiklooData2->getPositionLat());
        // $userPiklooData2->setLocation($location);
        // $userPiklooData2->setUser($pikloo2);
        // $this->matchGenerator->usersFinder($pikloo, $userPiklooData);
        // $manager->persist($userPiklooData2);

        // $commonPiklooTrack = new UserHasTrack();
        // $commonPikloo2Track = new UserHasTrack();
        // $commonTrack = $tracks[0];
        // $commonPiklooTrack->setUser($pikloo);
        // $commonPiklooTrack->setTrack($commonTrack);
        // $commonPikloo2Track->setUser($pikloo2);
        // $commonPikloo2Track->setTrack($commonTrack);
        // $manager->persist($commonPiklooTrack);
        // $manager->persist($commonPikloo2Track);

        // $match = new MatchUp();
        // $match->setUser($pikloo);
        // $match->setUserMatch($pikloo2);
        // $manager->persist($match);

        // $manager->flush();
    }
}
