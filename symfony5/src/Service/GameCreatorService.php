<?php
declare(strict_types=1);
namespace App\Service;

use App\Interfaces\IGameRepo;
use App\Entity\Game;

class GameCreatorService
{

	private $gameRepo;

	public function __construct(IGameRepo $gameRepo)
	{
		$this->gameRepo = $gameRepo;
	}

	/**
	 * #12 Set game board dimensions.
	 * 
	 * @param array $request
	 * @return Game
	 * @throws \App\Exception\GameValidatorException
	 */
	public function setBoardDimensions(?array $request): Game
	{
		if (!isset($request[Game::WIDTH])) {
			throw new \App\Exception\GameValidatorException([Game::WIDTH => Game::ERROR_HEIGHT_WIDTH_INVALID], Game::ERROR_HEIGHT_WIDTH_INVALID_CODE);
		}
		if (!isset($request[Game::HEIGHT])) {
			throw new \App\Exception\GameValidatorException([Game::HEIGHT => Game::ERROR_HEIGHT_WIDTH_INVALID], Game::ERROR_HEIGHT_WIDTH_INVALID_CODE);
		}
		try {
			return $this->gameRepo->setBoardDimensions($request[Game::WIDTH], $request[Game::HEIGHT]);
		} catch (\Error $ex) {
			throw new \App\Exception\GameValidatorException([Game::HEIGHT_WIDTH => Game::ERROR_HEIGHT_WIDTH_INVALID], Game::ERROR_HEIGHT_WIDTH_INVALID_CODE);
		}
	}
}
