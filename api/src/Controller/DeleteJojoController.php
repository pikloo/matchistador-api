<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
final class DeleteJojoController extends AbstractController
{
    #[Route(path: '/delete_jojo')]
    public function deleteJojo(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $jojo = $userRepository->findOneByEmail("jonathanluquet@gmail.com");
        if (!$jojo) return $this->json(["error" => "Jojo n'existe pas"], Response::HTTP_NOT_FOUND);

        $entityManager->remove($jojo);
        $entityManager->flush();
        return $this->json(["success" => "Jojo a bien été supprimé"], Response::HTTP_OK);
    }
}