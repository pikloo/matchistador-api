<?php

namespace App\Controller;

use App\Entity\UserData;
use App\Service\MatchGenerator;
use App\Service\ScoreCalculator;
use App\Repository\UserRepository;
use App\Service\AccountActivation;
use App\Repository\UserDataRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ActivationController extends AbstractController
{
  const WRONG_ACTIVATION_TOKEN_MESSAGE = "Code non valable";
  const SUCCESS_MESSAGE = "Un code de confirmation a été envoyé sur votre adresse email";
  const ERROR_MESSAGE = "Une erreur est survenue";

  public function __construct(
    private RequestStack $request,
    private UserDataRepository $userDataRepository,
    private EntityManagerInterface $entityManager,
    private UserRepository $userRepository,
    private TokenStorageInterface $tokenStorage,
    private AccountActivation $accountActivation,
    private MatchGenerator $matchGenerator,
    private ScoreCalculator $scoreCalculator
  ) {
  }
  #[Route(path: '/activate')]
  public function activate(): Response
  {
    try {
      $jsonContent = $this->request->getCurrentRequest()->toArray();
      $userToken = $jsonContent['token'];
      $userData = $this->userDataRepository->findOneBy(["activation_token" => $userToken]);
      if (!$userData instanceof UserData) return $this->json(["message" => self::WRONG_ACTIVATION_TOKEN_MESSAGE], Response::HTTP_NOT_FOUND);
      $userData->setActivationToken(null);

      $user = $userData->getUser();
      $user->setIsActive(true);
      $this->matchGenerator->usersFinder($user, $userData, 30, true);

      $this->entityManager->flush();

      //TODO générer et envoyer un refresh token ??
      return $this->json($userData->getUser(), Response::HTTP_OK);
    } catch (\Exception $e) {
      // dd($e);
      return $this->json(
        [
          "error" => $e->getMessage()
        ],
        Response::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }

  #[Route(path: '/activate/refresh')]
  public function refresh(): Response
  {
    //Trouver le user et générer un nouveau token
    $user = $this->userRepository->find($this->tokenStorage->getToken()->getUser()->getUserIdentifier());

    if (!$user instanceof JWTUser) throw new AuthenticationException;

    $userData = $user->getUserData();
    $token = $this->accountActivation->generateToken(6);
    $userData->setActivationToken($token);
    $this->entityManager->flush();

    //Envoyer un mail d'activation
    try {
      $this->accountActivation->sendEmail($user->getEmail(), $token);

      return $this->json(["message" => self::SUCCESS_MESSAGE], Response::HTTP_OK);
    } catch (\Exception $e) {

      return $this->json(
        [
          "message" => self::ERROR_MESSAGE,
          "error" => $e->getMessage()
        ],
        Response::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }
}
