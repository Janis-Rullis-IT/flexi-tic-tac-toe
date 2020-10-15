<?php
declare(strict_types=1);
namespace App\Tests;

use App\Entity\Game;
use App\Exception\GameValidatorException;
use App\Service\GameCreatorService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * #3 #12 Validate numbers.
 * 
 * * -1	a 0
 * 
 * * Set a limit. Otherwise it may complicate or even crash the system (100000x100000 grid) display and even the gameplay might be a nightmare.		2x2	20x20	20x19	19x20
 * 
 * * Grid has already been chosen					0
 */
class GameUnitTest extends KernelTestCase
{

	private $c;
	private $gameCreatorService;

	protected function setUp(): void
	{
		$kernel = self::bootKernel();
		$this->c = $kernel->getContainer();
		$this->gameCreatorService = $this->c->get('test.' . GameCreatorService::class);
	}

	public function testValidWith()
	{
		$width = 3;
		$game = new Game();
		$game->setWidth($width);
		$this->assertEquals($width, $game->getWidth());
	}

//	public function testWidthAlreadySet()
//	{
//		// #12 TODO: Load with status 'ongoing'.
////		$game
//		$this->expectException(GameValidatorException::class);
//		$this->expectExceptionCode(Game::ERROR_WIDTH_INVALID_CODE, Game::ERROR_WIDTH_INVALID);
//		$game->setWidth(3);
//	}


	public function testWidthNotInteger()
	{
		$game = new Game();

		$this->expectException(\TypeError::class);
		$game->setWidth('a');
	}

	public function testWidthNotInteger2()
	{
		$game = new Game();

		$this->expectException(\TypeError::class);
		$game->setWidth(3.9);
	}

	public function testWidthTooSmall()
	{
		$game = new Game();

		$this->expectException(GameValidatorException::class);
		$this->expectExceptionCode(Game::ERROR_WIDTH_INVALID_CODE, Game::ERROR_WIDTH_INVALID);
		$game->setWidth(Game::MIN_WIDTH - 1);
	}

	public function testWidthTooBig()
	{
		$game = new Game();

		$this->expectException(GameValidatorException::class);
		$this->expectExceptionCode(Game::ERROR_WIDTH_INVALID_CODE, Game::ERROR_WIDTH_INVALID);
		$game->setWidth(Game::MAX_WIDTH + 1);
	}
}
