<?php

namespace App\Controller;

use App\Entity\UserPicture;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
final class CreateUserPictureAction extends AbstractController
{
    public function __invoke(Request $request): UserPicture
    {
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }

        $userPicture = new UserPicture();
        $userPicture->file = $uploadedFile;

        return $userPicture;
    }
}