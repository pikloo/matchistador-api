<?php

namespace App\Controller;

use App\Entity\Track;
use App\Entity\MatchUp;
use App\Repository\MatchUpRepository;
use App\Repository\UserHasTrackRepository;
use App\Repository\UserHasMatchupRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class MatchTracksCollectionController extends AbstractController
{

    public function __construct(
        private UserHasMatchupRepository $userHasMatchupRepository,
        private MatchUpRepository $matchRepository,
        private UserHasTrackRepository $userHasTrackRepository,
    ) {
    }

    #[Route(
        name: 'api_match_tracks',
        // path: '/matchs/{id}/tracks',
        defaults: [
            "_api_collection_operation_name" => "api_match_tracks",
            "_api_resource_class"=>Track::class,
        ]
    )]

    public function getCommonsTracks(Request $request, MatchUp $match): Paginator
    {        
        $userA= $match->getUsersInMatch()[0]->getUser();
        $userB= $match->getUsersInMatch()[1]->getUser();
        
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 30);

        return $this->userHasTrackRepository->findCommonsTracksBetweenUsersCollection($userA, $userB, $page, $limit);
    }
}