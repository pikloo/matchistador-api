<?php

namespace App\Controller;

use App\Service\PlaylistSynchronizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrackController extends AbstractController
{
    public function __construct(
        private PlaylistSynchronizer $playlistSynchronizer,
        private RequestStack $requestStack
    ) {
    }

    #[Route(path: '/trackslist/initialize')]
    public function create(
        Request $request,
    ): JsonResponse {
        $jsonContent = $request->toArray();
        $user = $jsonContent['user'];
        $owner = $this->playlistSynchronizer->getUser($user);
        //TODO: vérifier que le owner est la Userne connectée (voters)
        $normalizedTracks = $this->playlistSynchronizer->trackJsonInitializer($jsonContent['tracks']);
        $resultsCheckTracksToCreate = $this->playlistSynchronizer->getTracksToCreateFromData($normalizedTracks);
        $this->playlistSynchronizer->saveTracks($owner, $resultsCheckTracksToCreate['tracksToCreate'], $resultsCheckTracksToCreate['tracksToAddToUserPlaylist']);
        return $this->json([
            "nb total de titres envoyées" => count($jsonContent['tracks']),
            "nb de titres dans la playlist user" => count($owner->getUserTracks()),
            // "errors" => $resultsCheckTracksToCreate['errors'],
        ], Response::HTTP_OK);
    }

    #[Route(path: '/trackslist/synchronize')]
    public function update(
        Request $request,
    ): JsonResponse {
        $jsonContent = $request->toArray();
        $user = $jsonContent['user'];
        $session = $this->requestStack->getSession();
        $owner = $this->playlistSynchronizer->getUser($user);
        $userTracks = $this->playlistSynchronizer->getActivedUserTracks($owner);
        
        //TODO: vérifier que le owner est la Userne connectée (voters)
        $normalizedTracks = $this->playlistSynchronizer->trackJsonInitializer($jsonContent['tracks']);
        $resultsCheckTracksToCreate = $this->playlistSynchronizer->getTracksToCreateFromData($normalizedTracks);
        // $sendTracks = array_map(fn ($array) => $array["track"], $resultsCheckTracksToCreate['tracksToAddToUserPlaylist']);
        $sendTracks = [];
        foreach ($resultsCheckTracksToCreate['tracksToAddToUserPlaylist'] as $trackToAdd){
            $sendTracks[] = $trackToAdd['track'];
        }

        // $sessionSendTracks = $session->get('sendTracks', null);
        // if(null === $sessionSendTracks){
        //     $session->set('sendTracks', $sendTracks);
        // }else {
        //     // dd($sessionSendTracks, $session->get('sendTracks'), $sendTracks);
        //     $session->set('sendTracks', array_merge($session->get('sendTracks'), $sendTracks));
        // }
        // $sendTracksCounter = count($session->get('sendTracks'));
        // if ($jsonContent['last']) {
            $tracksToDelete = $this->playlistSynchronizer->setUserTracksToDelete($sendTracks, $owner);
        //     $session->remove('sendTracks');
        // }

        $this->playlistSynchronizer->saveTracks($owner, $resultsCheckTracksToCreate['tracksToCreate'], $resultsCheckTracksToCreate['tracksToAddToUserPlaylist'], 'update');
        return $this->json([
            "nb total de titres envoyés" => count($sendTracks),
            "nb de titres dans la playlist user précédente" => count($userTracks),
            "nb de titres supprimées de la playlist user" => isset($tracksToDelete) ? count($tracksToDelete) : 0,
            "nb de titres dans la playlist user" => count($this->playlistSynchronizer->getActivedUserTracks($owner)),
            // "errors" => $resultsCheckTracksToCreate['errors'],
        ], Response::HTTP_OK);
    }
}
