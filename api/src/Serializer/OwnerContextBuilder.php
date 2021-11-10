<?php

namespace App\Serializer;

use App\Repository\UserRepository;
use App\Repository\UserDataRepository;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

//!A refaire s'en se servir du token!
final class OwnerContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(private SerializerContextBuilderInterface $decorated, private TokenStorageInterface $token, private UserRepository $userRepository, public UserDataRepository $userDataRepository)
    {
    }

    /**
     * @return mixed[]
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        $route = $request->get('_route');
        $id = isset($request->get('_route_params')['id']) ? $request->get('_route_params')['id'] : '';

        if (
            $route === 'api_users_get_item' ||
             $route === 'api_users_patch_item'
        ) {
            if ($this->token->getToken() === null) throw new AuthenticationException();
            
            $currentUser = $this->token->getToken()->getUser();
            $userToSerialize = $this->userRepository->find($request->get('id'));
            if ($currentUser->getUserIdentifier() === $userToSerialize->getId()) {
                $context['groups'][] = 'owner:read';
            }
        }

        if ($route === 'api_user_datas_get_item' &&  !preg_match('/[a-z0-9]{32}/', $id) || $route === 'api_user_datas_patch_item') {
            if ($this->token->getToken() === null) throw new AuthenticationException();
            $currentUser = $this->token->getToken()->getUser();
            $userDataToSerialize = $this->userDataRepository->find($request->get('id'));
            if ($currentUser->getUserIdentifier() === $userDataToSerialize->getUser()->getId()) {
                $context['groups'][] = 'owner:read';
            }
        }

        return $context;
    }
}
