<?php

namespace App\EventSubscriber;

use App\Entity\UserData;
use Doctrine\ORM\Events;
use App\Service\AccountActivation;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;

class TokenActivationSubscriber implements EventSubscriberInterface
{
  
  public function __construct(private AccountActivation $accountActivation)
  {

  }
  
  /**
   * @return string[]
   */
  public function getSubscribedEvents(): array
  {
    return [
      Events::prePersist, 
      Events::postPersist,
    ];
  }

  public function prePersist(LifecycleEventArgs $args): void
  {
    $userData = $args->getObject();
    if ($userData instanceof UserData) {
      $token = $this->accountActivation->generateToken(6);
      $userData->setActivationToken($token);
    }
  }

  public function postPersist(LifecycleEventArgs $args): void
  {
    $userData = $args->getObject();

    if ($userData instanceof UserData) {
      $user = $userData->getUser();
      // $this->accountActivation->sendEmail($user->getEmail(), $userData->getActivationToken());
    }
  }
}
