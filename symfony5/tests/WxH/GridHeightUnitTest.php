<?php

declare(strict_types=1);

namespace App\Tests\WxH;

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
class GridHeightUnitTest extends KernelTestCase
{
    private $c;
    private $gameCreatorService;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->c = $kernel->getContainer();
        $this->gameCreatorService = $this->c->get('test.'.GameCreatorService::class);
    }

    public function testValidHeight()
    {
        $width = 3;
        $game = new Game();
        $game->setHeight($width);
        $this->assertEquals($width, $game->getHeight());
    }

    // #12 TODO: Implement this later when status field is added.
    //	public function testHeightAlreadySet()
    //	{
    //		// #12 TODO: Load with status 'ongoing'.
    ////		$game
    //		$this->expectException(GameValidatorException::class);
    //		$this->expectExceptionCode(Game::ERROR_HEIGHT_WIDTH_INVALID_CODE, Game::ERROR_HEIGHT_WIDTH_INVALID);
    //		$game->setHeight(3);
    //	}

    public function testHeightNotInteger()
    {
        $game = new Game();

        $this->expectException(\TypeError::class);
        $game->setHeight('a');
    }

    public function testHeightNotInteger2()
    {
        $game = new Game();

        $this->expectException(\TypeError::class);
        $game->setHeight(3.9);
    }

    public function testHeightTooSmall()
    {
        $game = new Game();

        $this->expectException(GameValidatorException::class);
        $this->expectExceptionCode(Game::ERROR_HEIGHT_WIDTH_INVALID_CODE, Game::ERROR_HEIGHT_WIDTH_INVALID);
        $game->setHeight(Game::MIN_HEIGHT_WIDTH - 1);
    }

    public function testHeightTooBig()
    {
        $game = new Game();

        $this->expectException(GameValidatorException::class);
        $this->expectExceptionCode(Game::ERROR_HEIGHT_WIDTH_INVALID_CODE, Game::ERROR_HEIGHT_WIDTH_INVALID);
        $game->setHeight(Game::MAX_HEIGHT_WIDTH + 1);
    }
}
