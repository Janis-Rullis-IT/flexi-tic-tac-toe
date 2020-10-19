<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Move;
use App\Interfaces\IGameRepo;
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
     * Collect the current game (draft or ongoing - must be only 1).
     *
     * @Route("/game", name="getCurrentGame", methods={"GET"})
     * @SWG\Tag(name="1. game")
     *
     * @SWG\Response(response=200, description="", @SWG\Schema(type="object", ref=@Model(type=Game::class, groups={"PUB"})))
     * @SWG\Response(response=404, description="Not found.", @SWG\Schema(type="object", @SWG\Property(property="errors", type="object", example={ "id": "#14 Can not find the game."})))
     */
    public function getCurrentGame(IGameRepo $gameRepo): JsonResponse
    {
        try {
            $game = $gameRepo->getCurrent();
            if (empty($game)) {
                return $this->json(['errors' => [Game::ID => Game::ERROR_CAN_NOT_FIND]], Response::HTTP_NOT_FOUND);
            }

            return $this->json($game->toArray(), Response::HTTP_OK);
        } catch (\Exception $e) {
            if (method_exists($e, 'getErrors')) {
                return $this->json(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
            }

            return $this->json(['errors' => [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Submit the width and height for the new game's board.
     *
     * @Route("/game/grid", name="setBoardDimensions", methods={"POST"})
     * @SWG\Tag(name="1. game")
     *
     * @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(required={"width", "height"}, type="object", ref=@Model(type=Game::class, groups={"CREATE"})))
     * @SWG\Response(response=200, description="OK", @SWG\Schema(type="object", ref=@Model(type=Game::class, groups={"PUB"})))
     * @SWG\Response(response=400, description="Bad Request", @SWG\Schema(type="object", @SWG\Property(property="errors", type="object", example={"width": "#12 Width and height must be an integer from 2 to 20."})))
     */
    public function setBoardDimensions(Request $request, GameCreatorService $gameCreatorService): JsonResponse
    {
        try {
            $resp = $gameCreatorService->setBoardDimensions(json_decode($request->getContent(), true))->toArray();

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
     * @Route("/game/rules", name="setRules", methods={"PUT"})
     * @SWG\Tag(name="1. game")
     *
     * @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(required={"moves_to_win"}, type="object", ref=@Model(type=Game::class, groups={"PUB"})))
     * @SWG\Response(response=200, description="OK", @SWG\Schema(type="object", ref=@Model(type=Game::class, groups={"PUB"})))
     * @SWG\Response(response=400, description="Bad Request", @SWG\Schema(type="object", ref=@Model(type=Game::class, groups={"PUB"})))
     */
    public function setRules(Request $request, GameCreatorService $gameCreatorService): JsonResponse
    {
        try {
            $resp = $gameCreatorService->setRules(json_decode($request->getContent(), true));

            return $this->json($resp, Response::HTTP_OK);
        } catch (\Exception $e) {
            if (method_exists($e, 'getErrors')) {
                return $this->json(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
            }

            return $this->json(['errors' => [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Select the cell.
     *
     * @Route("/game/move", name="selectCell", methods={"POST"})
     * @SWG\Tag(name="1. game")
     *
     * @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(required={"row", "column"}, type="object", ref=@Model(type=Move::class, groups={"CREATE"})))
     * @SWG\Response(response=200, description="OK", @SWG\Schema(type="object", ref=@Model(type=Move::class, groups={"PUB"})))
     * @SWG\Response(response=400, description="Bad Request", @SWG\Schema(type="object", @SWG\Property(property="errors", type="object", example={"cell": "#12 Width and height must be an integer from 2 to 20."})))
     */
    public function selectCell(Request $request, MoveService $moveService): JsonResponse
    {
        try {
            $resp = $moveService->selectCell(json_decode($request->getContent(), true))->toArray();

            return $this->json($resp, Response::HTTP_OK);
        } catch (\Exception $e) {
            if (method_exists($e, 'getErrors')) {
                return $this->json(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
            }

            return $this->json(['errors' => [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }
}
