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
	 */
	public function setBoardDimensions(?array $request)
	{
		//int $width, int $height
		if (!isset($request[Game::WIDTH])) {
			throw new \App\Exception\GameValidatorException([Game::WIDTH => Game::ERROR_WIDTH_INVALID], Game::ERROR_WIDTH_INVALID_CODE);
		}
		try {
			$game = $this->gameRepo->setBoardDimensions($request[Game::WIDTH], $request[Game::WIDTH]);
		} catch (\Throwable $ex) {
			throw new \App\Exception\GameValidatorException([Game::WIDTH => Game::ERROR_WIDTH_INVALID], Game::ERROR_WIDTH_INVALID_CODE);
		}
	}
}
