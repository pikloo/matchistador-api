<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserHasMatchup;
use App\Repository\UserRepository;
use App\Repository\UserHasMatchupRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class UserMatchCollectionController extends AbstractController
{

    public function __construct(
        private UserHasMatchupRepository $userHasMatchupRepository,
        private UserRepository $userRepository
    ) {
    }

    #[Route(
        name: 'api_user_match',
        defaults: [
            "_api_collection_operation_name" => "api_user_match"
        ]
    )]

    public function inverseMatch(Request $request): Paginator
    {
        $userId = $request->attributes->get('id');
        $user = $this->userRepository->find($userId);
        
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 30);
        $isActive = (bool) $request->query->get('isActive', true);
        $orderByScore = (string) $request->query->get('orderByScore', null);
        $orderByUpdatedAt = (string) $request->query->get('orderByUpdatedAt', null);

        return $this->userHasMatchupRepository->findInverseMatchByUser($user, $page, $limit, $isActive,$orderByScore, $orderByUpdatedAt);
    }
}