<?php

namespace App\Controller;

use App\Service\GameCreatorService;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class GameController extends AbstractController
{
    /**
     * Submit the width and height for the new game's board.
     *
     * @Route("/game/grid", name="setBoardDimensions", methods={"POST"})
     * @SWG\Tag(name="1. game")
     *
     * @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(required={"width", "height"}, type="object")))
     */
    public function setBoardDimensions(Request $request, GameCreatorService $gameCreatorService): JsonResponse
    {
        try {
            $resp = $gameCreatorService->setBoardDimensions(json_decode($request->getContent(), true));

            return $this->json($resp, Response::HTTP_OK);
//        } catch (UidValidatorException $e) {
//            return $this->json($e->getErrors(), Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json($e->getErrors(), Response::HTTP_BAD_REQUEST);
        }
    }
}
