<?php

declare(strict_types=1);

namespace App\Tests\SelectedCell;

use App\Entity\Game;
use App\Entity\SelectedCell;
use App\Exception\SelectedCellValidatorException;
use App\Interfaces\IGameRepo;
use App\Interfaces\ISelectedCellRepo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RowUnitTest extends KernelTestCase
{
    private $c;
    private $gameRepo;
    private $selectedCellRepo;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->c = $kernel->getContainer();
        $this->gameRepo = $this->c->get('test.'.IGameRepo::class);
        $this->selectedCellRepo = $this->c->get('test.'.ISelectedCellRepo::class);
    }

    public function testValid()
    {
        $selectedCell = new SelectedCell();
        $game = new Game();
        $game->setStatus(Game::DRAFT);
        $game->setHeight(Game::MAX_HEIGHT_WIDTH);
        $game->setWidth(Game::MAX_HEIGHT_WIDTH);
        $game->setSelectedCellCntToWin(Game::MAX_HEIGHT_WIDTH);

        $game->setStatus(Game::ONGOING);
        $selectedCell->setRow($game, $selectedCell->getMaxAllowedRow($game));
        $this->assertEquals($selectedCell->getRow(), $selectedCell->getMaxAllowedRow($game));
    }

    public function testNotGame()
    {
        $selectedCell = new SelectedCell();
        $this->expectException(\TypeError::class);
        $selectedCell->setRow('a', 1);
    }

    public function testNotInteger()
    {
        $selectedCell = new SelectedCell();
        $this->expectException(\TypeError::class);
        $selectedCell->setRow(new Game(), 'a');
    }

    public function testNotInteger2()
    {
        $selectedCell = new SelectedCell();
        $this->expectException(\TypeError::class);
        $selectedCell->setRow(new Game(), 3.9);
    }

    public function testStatusNotSet()
    {
        $selectedCell = new SelectedCell();

        $this->expectException(SelectedCellValidatorException::class);
        $this->expectExceptionCode(SelectedCell::ERROR_SELECTED_CELL_ONLY_FOR_ONGOING_CODE, SelectedCell::ERROR_SELECTED_CELL_ONLY_FOR_ONGOING);
        $selectedCell->setRow(new Game(), SelectedCell::MIN_INDEX);
    }

    public function testInvalidStatusSet()
    {
        $selectedCell = new SelectedCell();
        $game = new Game();
        $game->setStatus(Game::DRAFT);

        $this->expectException(SelectedCellValidatorException::class);
        $this->expectExceptionCode(SelectedCell::ERROR_SELECTED_CELL_ONLY_FOR_ONGOING_CODE, SelectedCell::ERROR_SELECTED_CELL_ONLY_FOR_ONGOING);
        $selectedCell->setRow($game, SelectedCell::MIN_INDEX);
    }

    public function testTooSmall()
    {
        $selectedCell = new SelectedCell();
        $game = new Game();
        $game->setStatus(Game::DRAFT);
        $game->setHeight(Game::MAX_HEIGHT_WIDTH);
        $game->setWidth(Game::MAX_HEIGHT_WIDTH);
        $game->setSelectedCellCntToWin(Game::MAX_HEIGHT_WIDTH);

        $game->setStatus(Game::ONGOING);

        $this->expectException(SelectedCellValidatorException::class);
        $this->expectExceptionCode(SelectedCell::ERROR_SELECTED_CELL_INVALID_CODE, SelectedCell::ERROR_SELECTED_CELL_INVALID);
        $selectedCell->setRow($game, SelectedCell::MIN_INDEX - 1);
    }

    public function testTooBig()
    {
        $selectedCell = new SelectedCell();
        $game = new Game();
        $game->setStatus(Game::DRAFT);
        $game->setHeight(Game::MAX_HEIGHT_WIDTH);
        $game->setWidth(Game::MAX_HEIGHT_WIDTH);
        $game->setSelectedCellCntToWin(Game::MAX_HEIGHT_WIDTH);

        $game->setStatus(Game::ONGOING);

        $this->expectException(SelectedCellValidatorException::class);
        $this->expectExceptionCode(SelectedCell::ERROR_SELECTED_CELL_INVALID_CODE, SelectedCell::ERROR_SELECTED_CELL_INVALID);
        $selectedCell->setRow($game, $selectedCell->getMaxAllowedRow($game) + 1);
    }
}
