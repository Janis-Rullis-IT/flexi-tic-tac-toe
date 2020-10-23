<?php

declare(strict_types=1);

namespace App\Tests\SelectedCell\Win;

use App\Entity\Game;
use App\Entity\SelectedCell;
use App\Interfaces\IGameRepo;
use App\Interfaces\ISelectedCellRepo;
use App\Interfaces\IVictoryCalculationService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DiagonallyFromRightToLeftUnitTest extends KernelTestCase
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

    public function testValid()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH + 1, Game::MIN_HEIGHT_WIDTH + 1);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH + 1);
        $game->setSelectedCellCntToWin(Game::MIN_HEIGHT_WIDTH + 1);
        $game = $this->gameRepo->markAsStarted($game);

        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, Game::MIN_HEIGHT_WIDTH);
        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX + 1, Game::MIN_HEIGHT_WIDTH - 1);
        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX + 2, Game::MIN_HEIGHT_WIDTH - 2);

        $markedCells = $this->selectedCellRepo->getAll($game->getId(), SelectedCell::SYMBOL_X);
        $this->assertEquals(3, $this->victoryCalculationService->getSelectedCellCntDiagonallyFromRightToLeft(3, $game, $selectedCell, $markedCells));
        $this->assertTrue($this->victoryCalculationService->isDiagonalWin(3, $game, $selectedCell, $markedCells));
    }

    public function testNotEnough()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH + 1, Game::MIN_HEIGHT_WIDTH + 1);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH + 1);
        $game->setSelectedCellCntToWin(Game::MIN_HEIGHT_WIDTH + 1);
        $game = $this->gameRepo->markAsStarted($game);

        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, Game::MIN_HEIGHT_WIDTH);
        $selectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX + 1, Game::MIN_HEIGHT_WIDTH - 1);

        $markedCells = $this->selectedCellRepo->getAll($game->getId(), SelectedCell::SYMBOL_X);
        $this->assertEquals(1, $this->victoryCalculationService->getSelectedCellCntDiagonallyFromRightToLeft(2, $game, $selectedCell, $markedCells));
        $this->assertFalse($this->victoryCalculationService->isDiagonalWin(2, $game, $selectedCell, $markedCells));
    }
}
