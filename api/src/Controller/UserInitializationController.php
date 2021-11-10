<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\RefreshToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class UserInitializationController extends AbstractController
{

  public function __construct(
    private RequestStack $request,
    private SerializerInterface $serializer,
    private ValidatorInterface $validator,
    private JWTTokenManagerInterface $JWTManager,
    private EntityManagerInterface $entityManager,
    private RefreshToken $refreshTokenManager,
  ) {
  }
  #[Route(path: '/init')]
  public function __invoke(): Response
  {
    $jsonContent = $this->request->getCurrentRequest()->getContent();
    $user = new User;
    $user = $this->serializer->deserialize($jsonContent, User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);
    $errors = $this->validator->validate($user);
    $errorsList = [];

    if (count($errors) === 0) {
      $this->entityManager->persist($user);
      $token = $this->JWTManager->create($user);
      $refreshToken = $this->refreshTokenManager->create($user);

      return $this->json([
        "token" => $token,
        "refresh_token" => $refreshToken,
      ], Response::HTTP_CREATED);
    } else {
      foreach ($errors as $error) {
        $errorsList[$error->getPropertyPath()] = $error->getMessage();
      }

      return $this->json(["errors" => $errorsList], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
  }
}
