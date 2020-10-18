<?php

namespace App\Controller;

use App\Entity\Game;
use App\Service\GameCreatorService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    /**
     * Submit the width and height for the new game's board.
     *
     * @Route("/game/grid", name="setBoardDimensions", methods={"POST"})
     * @SWG\Tag(name="1. game")
     *
     * @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(required={"width", "height"}, type="object", ref=@Model(type=Game::class, groups={"PUB"})))
     * @SWG\Response(response=200, description="Created.", @SWG\Schema(type="object", ref=@Model(type=Game::class, groups={"PUB"})))
     * @SWG\Response(response=404, description="Not found.", @SWG\Schema(type="object", ref=@Model(type=Game::class, groups={"PUB"})))
     */
    public function setBoardDimensions(Request $request, GameCreatorService $gameCreatorService): JsonResponse
    {
        try {
            $resp = $gameCreatorService->setBoardDimensions(json_decode($request->getContent(), true));

            return $this->json($resp, Response::HTTP_OK);
        } catch (\Exception $e) {
            if (method_exists($e, 'getErrors')) {
                return $this->json(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
            }

            return $this->json(['errors' => [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Set game rules like how many moves are required to win.
     *
     * @Route("/game/{gameId}rules", name="setRules", methods={"PUT"})
     * @SWG\Tag(name="1. game")
     *
     * @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(required={"moves_to_win"}, type="object", ref=@Model(type=Game::class, groups={"PUB"})))
     * @SWG\Response(response=200, description="Saved.", @SWG\Schema(type="object", ref=@Model(type=Game::class, groups={"PUB"})))
     * @SWG\Response(response=404, description="Not found.", @SWG\Schema(type="object", ref=@Model(type=Game::class, groups={"PUB"})))
     */
    public function setRules(Request $request, GameCreatorService $gameCreatorService, int $gameId): JsonResponse
    {
        try {
            $resp = $gameCreatorService->setRules($gameId, json_decode($request->getContent(), true));

            return $this->json($resp, Response::HTTP_OK);
        } catch (\Exception $e) {
            if (method_exists($e, 'getErrors')) {
                return $this->json(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
            }

            return $this->json(['errors' => [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }
}
