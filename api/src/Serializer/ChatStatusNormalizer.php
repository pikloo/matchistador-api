<?php

namespace App\Serializer;

use App\Entity\Message;
use App\Entity\TalkUser;
use App\Entity\MessageUser;
use App\Repository\TalkUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MessageUserRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Encoder\NormalizationAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

final class ChatStatusNormalizer implements ContextAwareNormalizerInterface, NormalizationAwareInterface, NormalizerAwareInterface
{
  use NormalizerAwareTrait;

  private const ALREADY_CALLED = 'CHAT_STATUS_NORMALIZER_ALREADY_CALLED';

  public function __construct(
    private TalkUserRepository $talkUserRepository,
    private MessageUserRepository $messageUserRepository,
    private EntityManagerInterface $_em,
    private Security $security,
  ) {
  }

  public function normalize($object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
  {
    $context[self::ALREADY_CALLED] = true;


    /** @var Message $message */
    $message = $object;
    $user = $this->security->getUser()->getUserIdentifier();
    $talk = $message->getTalk();
    $talkUserReadingStatus = $this->talkUserRepository->findStatusByTalkAndUser($talk, $user);
    $talkUserReadingStatus->setReadingStatus(TalkUser::READ);

    foreach ($talk->getMessages() as $message){
      $messageUserReadingStatus = $this->messageUserRepository->findStatusByMessageAndUser($message, $user);
      $messageUserReadingStatus->setReadingStatus(MessageUser::READ);
      $this->_em->persist($messageUserReadingStatus);
    }

    $this->_em->persist($talkUserReadingStatus);
    $this->_em->flush();

    return $this->normalizer->normalize($object, $format, $context);
  }

  public function supportsNormalization($data, ?string $format = null, array $context = []): bool
  {
    if (isset($context[self::ALREADY_CALLED])) {
      return false;
    }

    return $data instanceof Message;
  }
}
