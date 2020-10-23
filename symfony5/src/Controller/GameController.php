<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\SelectedCell;
use App\Interfaces\IGameRepo;
use App\Interfaces\IGameService;
use App\Interfaces\ISelectedCellService;
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
     * Start the game.
     *
     * @Route("/game", name="start", methods={"POST"})
     * @SWG\Tag(name="1. game")
     *
     * @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(required={"width", "height", "move_cnt_to_win"}, type="object", ref=@Model(type=Game::class, groups={"CREATE"})))
     * @SWG\Response(response=200, description="OK", @SWG\Schema(type="object", ref=@Model(type=Game::class, groups={"CREATE_PUB"})))
     * @SWG\Response(response=400, description="Bad Request", @SWG\Schema(type="object", @SWG\Property(property="errors", type="object", example={"width": "#12 Width and height must be an integer from 2 to 20."})))
     */
    public function start(Request $request, IGameService $gameService): JsonResponse
    {
        try {
            $resp = $gameService->start(json_decode($request->getContent(), true))->toArray();

            return $this->json($resp, Response::HTTP_OK);
        } catch (\Exception $e) {
            if (method_exists($e, 'getErrors')) {
                return $this->json(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
            }

            return $this->json(['errors' => [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }

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

            return $this->json($game->toArray([], [Game::SELECTED_CELLS]), Response::HTTP_OK);
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
     * @SWG\Tag(name="2. other")
     *
     * @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(required={"width", "height"}, type="object", ref=@Model(type=Game::class, groups={"BOARD"})))
     * @SWG\Response(response=200, description="OK", @SWG\Schema(type="object", ref=@Model(type=Game::class, groups={"CREATE_PUB"})))
     * @SWG\Response(response=400, description="Bad Request", @SWG\Schema(type="object", @SWG\Property(property="errors", type="object", example={"width": "#12 Width and height must be an integer from 2 to 20."})))
     */
    public function setBoardDimensions(Request $request, IGameService $gameService): JsonResponse
    {
        try {
            $resp = $gameService->setBoardDimensions(json_decode($request->getContent(), true))->toArray();

            return $this->json($resp, Response::HTTP_OK);
        } catch (\Exception $e) {
            if (method_exists($e, 'getErrors')) {
                return $this->json(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
            }

            return $this->json(['errors' => [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Set game rules like how many SelectedCells are required to win.
     *
     * @Route("/game/rules", name="setRules", methods={"PUT"})
     * @SWG\Tag(name="2. other")
     *
     * @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(required={"move_cnt_to_win"}, type="object", ref=@Model(type=Game::class, groups={"RULES"})))
     * @SWG\Response(response=200, description="OK", @SWG\Schema(type="object", ref=@Model(type=Game::class, groups={"CREATE_PUB"})))
     * @SWG\Response(response=400, description="Bad Request", @SWG\Schema(type="object", @SWG\Property(property="errors", type="object", example={"move_cnt_to_win": "#15 move_cnt_to_win count to win must be an integer not smaller than 2 and not bigger than the height or width."})))
     */
    public function setRules(Request $request, IGameService $gameService): JsonResponse
    {
        try {
            $resp = $gameService->setRules(json_decode($request->getContent(), true))->toArray();

            return $this->json($resp, Response::HTTP_OK);
        } catch (\Exception $e) {
            if (method_exists($e, 'getErrors')) {
                return $this->json(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
            }

            return $this->json(['errors' => [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Mark game as started ('ongoing').
     *
     * @Route("/game/ongoing", name="markAsStarted", methods={"PUT"})
     * @SWG\Tag(name="2. other")
     *
     * @SWG\Response(response=200, description="OK", @SWG\Schema(type="object", ref=@Model(type=Game::class, groups={"CREATE_PUB"})))
     * @SWG\Response(response=400, description="Bad Request", @SWG\Schema(type="object", ref=@Model(type=Game::class, groups={"CREATE_PUB"})))
     */
    public function markAsStarted(IGameRepo $gameRepo): JsonResponse
    {
        try {
            $game = $gameRepo->getCurrent();
            if (empty($game)) {
                return $this->json(['errors' => [Game::ID => Game::ERROR_CAN_NOT_FIND]], Response::HTTP_NOT_FOUND);
            }
            $game = $gameRepo->markAsStarted($game);

            return $this->json($game->toArray(), Response::HTTP_OK);
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
     * @Route("/game/select_cell", name="select", methods={"POST"})
     * @SWG\Tag(name="1. game")
     *
     * @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(required={"row", "column"}, type="object", ref=@Model(type=SelectedCell::class, groups={"CREATE"})))
     * @SWG\Response(response=200, description="OK", @SWG\Schema(type="object", ref=@Model(type=SelectedCell::class, groups={"PUB"})))
     * @SWG\Response(response=400, description="Bad Request", @SWG\Schema(type="object", @SWG\Property(property="errors", type="object", example={"cell": "#12 Width and height must be an integer from 2 to 20."})))
     */
    public function select(Request $request, ISelectedCellService $selectedCellService): JsonResponse
    {
        try {
            $resp = $selectedCellService->select(json_decode($request->getContent(), true))->toArray();

            return $this->json($resp, Response::HTTP_OK);
        } catch (\Exception $e) {
            if (method_exists($e, 'getErrors')) {
                return $this->json(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
            }

            return $this->json(['errors' => [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }
}
