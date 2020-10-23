<?php

declare(strict_types=1);

namespace App\Tests\SelectedCell\Win;

use App\Entity\Game;
use App\Entity\SelectedCell;
use App\Interfaces\IGameRepo;
use App\Interfaces\ISelectedCellRepo;
use App\Service\SelectedCellService;
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
        $this->SelectedCellService = $this->c->get('test.'.SelectedCellService::class);
    }

    public function testSelectedInTheRow()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH, Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH);
        $game->setSelectedCellCntToWin(Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->markAsStarted($game);

        $SelectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, SelectedCell::MIN_INDEX);
        $SelectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, SelectedCell::MIN_INDEX + 1);

        $SelectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX + 1, SelectedCell::MIN_INDEX + 1);

        $markedCells = $this->selectedCellRepo->getFromRow($game->getId(), SelectedCell::SYMBOL_X, SelectedCell::MIN_INDEX);
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
        $SelectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, SelectedCell::MIN_INDEX);
        $SelectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, SelectedCell::MIN_INDEX + 1);

        $markedCells = $this->selectedCellRepo->getFromRow($game->getId(), SelectedCell::SYMBOL_X, $SelectedCell->getRow());
        $this->assertTrue($this->SelectedCellService->isRowWin(2, $game, $SelectedCell, $markedCells));
    }

    public function testNotWin()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH + 1, Game::MIN_HEIGHT_WIDTH + 1);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH + 1);
        $game->setSelectedCellCntToWin(Game::MIN_HEIGHT_WIDTH + 1);
        $game = $this->gameRepo->markAsStarted($game);
        $SelectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, SelectedCell::MIN_INDEX);
        $SelectedCell = $this->selectedCellRepo->select($game, SelectedCell::MIN_INDEX, SelectedCell::MIN_INDEX + 1);

        $markedCells = $this->selectedCellRepo->getFromRow($game->getId(), SelectedCell::SYMBOL_X, $SelectedCell->getRow());
        $this->assertFalse($this->SelectedCellService->isRowWin(2, $game, $SelectedCell, $markedCells));
    }
}
