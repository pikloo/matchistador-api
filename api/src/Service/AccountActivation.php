<?php

namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class AccountActivation
{
  public function __construct(private MailerInterface $mailer, private ContainerBagInterface $params)
  {
  }
  
  public function generateToken($length): string
  {
    $randomBytes = openssl_random_pseudo_bytes($length);
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $token = '';
    for ($i = 0; $i < $length; $i++)
        $token .= $characters[ord($randomBytes[$i]) % $charactersLength];

    return $token;
  }

  public function sendEmail($userEmail, $userActivationToken): void
  {
    $sender = $this->params->get('app.server_mail_address');
    $email = (new Email())
      ->from($sender)
      ->to($userEmail)
      ->subject('Test mail')
      ->text('Code d\'activation ' . $userActivationToken);

    $this->mailer->send($email);
  }
}