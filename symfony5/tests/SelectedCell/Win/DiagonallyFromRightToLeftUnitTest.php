<?php

declare(strict_types=1);

namespace App\Tests\SelectedCell\Win;

use App\Entity\Game;
use App\Entity\SelectedCell;
use App\Interfaces\IGameRepo;
use App\Interfaces\ISelectedCellRepo;
use App\Service\SelectedCellService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DiagonallyFromRightToLeftUnitTest extends KernelTestCase
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
        $this->SelectedCellService = $this->c->get('test.'.SelectedCellService::class);
    }

    public function testValid()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH + 1, Game::MIN_HEIGHT_WIDTH + 1);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH + 1);
        $game->setSelectedCellCntToWin(Game::MIN_HEIGHT_WIDTH + 1);
        $game = $this->gameRepo->markAsStarted($game);

        $SelectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, Game::MIN_HEIGHT_WIDTH);
        $SelectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX + 1, Game::MIN_HEIGHT_WIDTH - 1);
        $SelectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX + 2, Game::MIN_HEIGHT_WIDTH - 2);

        $markedCells = $this->selectedCellRepo->getAll($game->getId(), SelectedCell::SYMBOL_X);
        $this->assertEquals(3, $this->SelectedCellService->getMarkedCellCntDiagonallyFromRightToLeft(3, $game, $SelectedCell, $markedCells));
        $this->assertTrue($this->SelectedCellService->isDiagonalWin(3, $game, $SelectedCell, $markedCells));
    }

    public function testNotEnough()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH + 1, Game::MIN_HEIGHT_WIDTH + 1);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH + 1);
        $game->setSelectedCellCntToWin(Game::MIN_HEIGHT_WIDTH + 1);
        $game = $this->gameRepo->markAsStarted($game);

        $SelectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, Game::MIN_HEIGHT_WIDTH);
        $SelectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX + 1, Game::MIN_HEIGHT_WIDTH - 1);

        $markedCells = $this->selectedCellRepo->getAll($game->getId(), SelectedCell::SYMBOL_X);
        $this->assertEquals(1, $this->SelectedCellService->getMarkedCellCntDiagonallyFromRightToLeft(2, $game, $SelectedCell, $markedCells));
        $this->assertFalse($this->SelectedCellService->isDiagonalWin(2, $game, $SelectedCell, $markedCells));
    }
}
