<?php
namespace App\Service;

use App\Interfaces\IGameRepo;

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
	 * @param int $width
	 * @param int $height
	 */
	public function setBoardDimensions(int $width, int $height)
	{
		$game = $this->gameRepo->setBoardDimensions($width, $height);
	}
}
