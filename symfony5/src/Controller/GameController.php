<?php
namespace App\Controller;

use App\Service\GameCreatorService;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
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
	 */
	public function setBoardDimensions(Request $request, GameCreatorService $gameCreatorService): JsonResponse
	{
		try {
			$resp = $gameCreatorService->setBoardDimensions(json_decode($request->getContent(), true));

			return $this->json($resp, Response::HTTP_OK);
		} catch (\Exception $e) {
			if (method_exists($e, 'getErrors')) {
				return $this->json(['errors'=> $e->getErrors()], Response::HTTP_BAD_REQUEST);
			}
			return $this->json(['errors'=> [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
		}
	}
}
