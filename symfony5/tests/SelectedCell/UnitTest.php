<?php

declare(strict_types=1);

namespace App\Tests\SelectedCell;

use App\Entity\Game;
use App\Entity\SelectedCell;
use App\Exception\SelectedCellValidatorException;
use App\Interfaces\IGameRepo;
use App\Interfaces\ISelectedCellRepo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UnitTest extends KernelTestCase
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

    public function testgetAll()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH, Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH);
        $game->setSelectedCellCntToWin(Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->markAsStarted($game);
        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, SelectedCell::MIN_INDEX);
        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, SelectedCell::MIN_INDEX + 1);
        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX + 1, SelectedCell::MIN_INDEX);

        $cells = $this->selectedCellRepo->getOrderedByRows($game->getId(), SelectedCell::SYMBOL_X);
        $this->assertEquals($cells[0][SelectedCell::ROW], SelectedCell::MIN_INDEX);
        $this->assertEquals($cells[1][SelectedCell::ROW], SelectedCell::MIN_INDEX);
        $this->assertEquals($cells[0][SelectedCell::COLUMN], SelectedCell::MIN_INDEX);
        $this->assertEquals($cells[1][SelectedCell::COLUMN], SelectedCell::MIN_INDEX + 1);

        $markedCells = $this->selectedCellRepo->getAll($game->getId(), SelectedCell::SYMBOL_X);
        $this->assertTrue(isset($markedCells[SelectedCell::MIN_INDEX][SelectedCell::MIN_INDEX]));
        $this->assertTrue(isset($markedCells[SelectedCell::MIN_INDEX][SelectedCell::MIN_INDEX + 1]));
        $this->assertTrue(isset($markedCells[SelectedCell::MIN_INDEX + 1][SelectedCell::MIN_INDEX]));

        $this->assertEquals(3, $this->selectedCellRepo->getTotalCnt($game->getId()));
    }

    public function testValid()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH, Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH);
        $game->setSelectedCellCntToWin(Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->markAsStarted($game);
        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, SelectedCell::MIN_INDEX);
        $this->assertEquals($selectedCell->getSymbol(), SelectedCell::SYMBOL_X);
        $this->assertEquals(1, $this->selectedCellRepo->getTotalCnt($game->getId()));
    }

    public function testCellAlreadyTaken()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH, Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH);
        $game->setSelectedCellCntToWin(Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->markAsStarted($game);
        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, SelectedCell::MIN_INDEX);

        $this->expectException(SelectedCellValidatorException::class);
        $this->expectExceptionCode(SelectedCell::ERROR_SELECTED_CELL_ALREADY_TAKEN_INVALID_CODE, SelectedCell::ERROR_SELECTED_CELL_ALREADY_TAKEN_INVALID);
        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, SelectedCell::MIN_INDEX);
    }
}
