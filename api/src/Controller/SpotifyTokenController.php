<?php
declare(strict_types=1);

namespace App\Controller;

use Kerox\OAuth2\Client\Provider\Spotify;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SpotifyTokenController extends AbstractController
{
    #[Route(path: '/spotify/token', name: 'spotify_token')]
    public function index(Request $request, Spotify $client) : JsonResponse
    {
        return new JsonResponse(
            $client->getAccessToken('authorization_code', [
                'code' => $request->request->get('code'),
            ])
        );
    }
}
