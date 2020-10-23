<?php

declare(strict_types=1);

namespace App\Tests\SelectedCell\Win;

use App\Entity\Game;
use App\Entity\SelectedCell;
use App\Interfaces\IGameRepo;
use App\Interfaces\ISelectedCellRepo;
use App\Interfaces\SelectedCell\IVictoryCalculationService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ColumnUnitTest extends KernelTestCase
{
    private $c;
    private $gameRepo;
    private $selectedCellRepo;
    private $victoryCalculationService;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->c = $kernel->getContainer();
        $this->gameRepo = $this->c->get('test.'.IGameRepo::class);
        $this->selectedCellRepo = $this->c->get('test.'.ISelectedCellRepo::class);
        $this->victoryCalculationService = $this->c->get('test.'.IVictoryCalculationService::class);
    }

    public function testSelectedInTheCol()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH, Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH);
        $game->setSelectedCellCntToWin(Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->markAsStarted($game);

        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, SelectedCell::MIN_INDEX);
        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, SelectedCell::MIN_INDEX + 1);

        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX + 1, SelectedCell::MIN_INDEX + 1);

        $markedCells = $this->selectedCellRepo->getFromColumn($game->getId(), SelectedCell::SYMBOL_X, SelectedCell::MIN_INDEX + 1);
        $this->assertEquals(count($markedCells), 2);
    }

    public function testWin()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH, Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH);
        $game->setSelectedCellCntToWin(Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->markAsStarted($game);
        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, SelectedCell::MIN_INDEX);
        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX + 1, SelectedCell::MIN_INDEX);

        $markedCells = $this->selectedCellRepo->getFromColumn($game->getId(), SelectedCell::SYMBOL_X, $selectedCell->getColumn());
        $this->assertTrue($this->victoryCalculationService->isColumnWin(2, $game, $selectedCell, $markedCells));
    }

    public function testNotWin()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH + 1, Game::MIN_HEIGHT_WIDTH + 1);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH + 1);
        $game->setSelectedCellCntToWin(Game::MIN_HEIGHT_WIDTH + 1);
        $game = $this->gameRepo->markAsStarted($game);
        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, SelectedCell::MIN_INDEX);
        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX + 1, SelectedCell::MIN_INDEX);

        $markedCells = $this->selectedCellRepo->getFromColumn($game->getId(), SelectedCell::SYMBOL_X, $selectedCell->getColumn());
        $this->assertFalse($this->victoryCalculationService->isColumnWin(2, $game, $selectedCell, $markedCells));
    }
}
