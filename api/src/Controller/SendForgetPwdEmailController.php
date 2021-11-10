<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SendForgetPwdEmailController extends AbstractController
{
  #[\Symfony\Component\Routing\Annotation\Route(name: 'send_forget_pwd_email', path: '/send_forget_pwd_email', methods: ['POST'])]
  public function sendEmail(Request $request, UserRepository $userRepository, MailerInterface $mailer, SerializerInterface $serializer, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $entityManager) : \Symfony\Component\HttpFoundation\JsonResponse
  {
      $jsonContent = $request->getContent();
      $user = $serializer->deserialize($jsonContent, User::class, 'json');
      $user = $userRepository->findOneBy(['email' => $user->getEmail()]);
      if (!$user instanceof \App\Entity\User) return $this->json(["erreur" => "Utilisateur non trouvé"], Response::HTTP_NOT_FOUND);
      //On génère un token
      $token = $tokenGenerator->generateToken();
      try {
        $user->setResetToken($token);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
      } catch (\Exception $e) {
        $this->json(['erreur' =>  $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
      }
      //URL de réinitialisation de mdp
      //TODO faire un var d'environnement
      $url = "https://matchistador.com/reset-password/" . $token;
      $forgetPwdEmail = (new Email())
        ->from('bb9b428c7c-75758b@inbox.mailtrap.io')
        ->to($user->getEmail())
        ->subject('Test mail forget mdp')
        ->text('url : ' . $url);
      $mailer->send($forgetPwdEmail);
      return $this->json(['message' => 'E-mail de réinitialisation du mot de passe envoyé.'], Response::HTTP_OK);
  }

  #[\Symfony\Component\Routing\Annotation\Route(path: '/check_reset_pwd_token/{token}')]
  public function checkResetPassword(Request $request, UserRepository $userRepository) : \Symfony\Component\HttpFoundation\JsonResponse
  {
      // On cherche un utilisateur avec le token donné
      $user = $userRepository->findOneBy(['reset_token' => $request->attributes->get('token')]);
      return ($user instanceof \App\Entity\User)
        ? $this->json(
          [
            "id" => $user->getId(),
            "name" => $user->getUserData()->getName()
          ],
          Response::HTTP_OK
        )
        :  $this->json(["erreur" => "Token non trouvé"], Response::HTTP_NOT_FOUND);
  }

  #[\Symfony\Component\Routing\Annotation\Route(path: '/reset_pwd/{id}', name: 'reset_pwd', methods: ['POST'])]
  public function resetPassword(Request $request, UserRepository $userRepository) : \Symfony\Component\HttpFoundation\JsonResponse
  {
      $user = $userRepository->findOneBy(['id' => $request->attributes->get('id')]);
      if (!$user instanceof \App\Entity\User) return $this->json(["erreur" => "Utilisateur non trouvé"], Response::HTTP_NOT_FOUND);
      // On supprime le token
      $user->setResetToken(null);
      //TODO : vérifier que le password confirm est identique au mdp
      // On chiffre le mot de passe
      $user->setPlainPassword(($request->toArray()['plainPassword']));
      dd($user);
      // On stocke
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($user);
      $entityManager->flush();
      return $this->json(["message" => "le mot de passe a bien été modifié"], Response::HTTP_OK);
  }
}
