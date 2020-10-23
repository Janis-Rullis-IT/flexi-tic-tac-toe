<?php

declare(strict_types=1);

namespace App\Tests\Rules;

use App\Entity\Game;
use App\Exception\GameValidatorException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SelectedCellCntToWinUnitTest extends KernelTestCase
{
    private $c;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->c = $kernel->getContainer();
    }

    public function testValid()
    {
        $game = new Game();
        $game->setStatus(Game::DRAFT);
        $game->setHeight(Game::MIN_HEIGHT_WIDTH);
        $game->setWidth(Game::MAX_HEIGHT_WIDTH);
        $game->setSelectedCellCntToWin(Game::MAX_HEIGHT_WIDTH);
        $this->assertEquals($game->getSelectedCellCntToWin(), Game::MAX_HEIGHT_WIDTH);
    }

    public function testValid2()
    {
        $game = new Game();
        $game->setStatus(Game::DRAFT);
        $game->setHeight(Game::MIN_HEIGHT_WIDTH);
        $game->setWidth(Game::MAX_HEIGHT_WIDTH);
        $game->setSelectedCellCntToWin(Game::MIN_HEIGHT_WIDTH);
        $this->assertEquals($game->getSelectedCellCntToWin(), Game::MIN_HEIGHT_WIDTH);
    }

    public function testNotInteger()
    {
        $game = new Game();

        $this->expectException(\TypeError::class);
        $game->setSelectedCellCntToWin('a');
    }

    public function testNotInteger2()
    {
        $game = new Game();

        $this->expectException(\TypeError::class);
        $game->setSelectedCellCntToWin(3.9);
    }

    public function testInvalidStatusSet()
    {
        $game = new Game();
        $this->expectException(GameValidatorException::class);
        $this->expectExceptionCode(Game::ERROR_STATUS_ONGOING_INVALID_CODE, Game::ERROR_STATUS_ONGOING_INVALID);
        $game->setStatus(Game::ONGOING);
        $game->setHeight(Game::MIN_HEIGHT_WIDTH);
        $game->setWidth(Game::MIN_HEIGHT_WIDTH);
        $game->setSelectedCellCntToWin(Game::MIN_HEIGHT_WIDTH);
    }

    public function testTooSmall()
    {
        $game = new Game();
        $game->setStatus(Game::DRAFT);
        $game->setHeight(Game::MIN_HEIGHT_WIDTH);
        $game->setWidth(Game::MIN_HEIGHT_WIDTH);
        $this->expectException(GameValidatorException::class);
        $this->expectExceptionCode(Game::ERROR_SELECTED_CELL_CNT_TO_WIN_INVALID_CODE, Game::ERROR_SELECTED_CELL_CNT_TO_WIN_INVALID);
        $game->setSelectedCellCntToWin(Game::MIN_HEIGHT_WIDTH - 1);
    }

    public function testTooBig()
    {
        $game = new Game();
        $game->setStatus(Game::DRAFT);
        $game->setHeight(Game::MIN_HEIGHT_WIDTH);
        $game->setWidth(Game::MAX_HEIGHT_WIDTH);
        $this->expectException(GameValidatorException::class);
        $this->expectExceptionCode(Game::ERROR_SELECTED_CELL_CNT_TO_WIN_INVALID_CODE, Game::ERROR_SELECTED_CELL_CNT_TO_WIN_INVALID);
        $game->setSelectedCellCntToWin(Game::MAX_HEIGHT_WIDTH + 1);
    }
}
