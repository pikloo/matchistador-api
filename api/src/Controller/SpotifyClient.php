<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SpotifyClient extends AbstractController
{
  public function __construct(private HttpClientInterface $client)
  {
  }

  #[Route(path: '/spotify/oauth/token', name: 'spotify_oauth_token', methods: 'GET')]
  public function getSpotifyToken(Request $request) : \Symfony\Component\HttpFoundation\JsonResponse
  {
      $code = $request->$request->get('code');
      $body = 'grant_type=authorization_code&code=' . $code . '&redirect_uri=' . $this->getParameter('SpotifyClientRedirectUri');
      $response = $this->client->request(
        'POST',
        'https://accounts.spotify.com/api/token',
        ['body' => $body],
        [
          'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode($this->getParameter('SpotifyClientID') . ':' . $this->getParameter('SpotifyClientSecret'))
          ],
  
        ]
      );
      $statusCode = $response->getStatusCode();
      $content = $response->getContent(false);
      // $statusCode = 200
      return $this->json($content, $statusCode);
  }
}
