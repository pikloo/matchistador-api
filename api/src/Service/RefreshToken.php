<?php

namespace App\Service;

use DateTime;
use DateInterval;
use App\Entity\User;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;

final class RefreshToken {
  public function __construct(RefreshTokenManagerInterface $refreshTokenManager)
  {
      $this->refreshTokenManager = $refreshTokenManager;
  }

  public function create(User $user)
  {
      $valid = new DateTime('now');
      $valid->add(new DateInterval('P3D'));

      $refreshToken = $this->refreshTokenManager->create();
      $refreshToken->setUsername($user->getUserIdentifier());
      $refreshToken->setRefreshToken();
      $refreshToken->setValid($valid);

      $this->refreshTokenManager->save($refreshToken);

      return $refreshToken->getRefreshToken();
  }
}
