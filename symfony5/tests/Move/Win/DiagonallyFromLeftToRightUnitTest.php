<?php

declare(strict_types=1);

namespace App\Tests\Move\Win;

use App\Entity\Game;
use App\Entity\Move;
use App\Interfaces\IGameRepo;
use App\Interfaces\IMoveRepo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DiagonallyFromLeftToRightUnitTest extends KernelTestCase
{
    private $c;
    private $gameRepo;
    private $moveRepo;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->c = $kernel->getContainer();
        $this->gameRepo = $this->c->get('test.'.IGameRepo::class);
        $this->moveRepo = $this->c->get('test.'.IMoveRepo::class);
    }

    public function testValid()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH + 1, Game::MIN_HEIGHT_WIDTH + 1);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH + 1);
        $game->setMoveCntToWin(Game::MIN_HEIGHT_WIDTH + 1);
        $game = $this->gameRepo->markAsStarted($game);

        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX, Move::MIN_INDEX);
        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX + 1, Move::MIN_INDEX + 1);
        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX + 2, Move::MIN_INDEX + 2);

        $markedCells = $this->moveRepo->getMarkedCells($game->getId(), Move::SYMBOL_X);
        $this->assertEquals(3, $this->moveRepo->getMarkedCellCntDiagonallyFromLeftToRight($game, $move, $markedCells));
    }

    public function testNotEnough()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH + 1, Game::MIN_HEIGHT_WIDTH + 1);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH + 1);
        $game->setMoveCntToWin(Game::MIN_HEIGHT_WIDTH + 1);
        $game = $this->gameRepo->markAsStarted($game);

        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX, Move::MIN_INDEX);
        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX + 1, Move::MIN_INDEX + 1);

        $markedCells = $this->moveRepo->getMarkedCells($game->getId(), Move::SYMBOL_X);
        $this->assertEquals(1, $this->moveRepo->getMarkedCellCntDiagonallyFromLeftToRight($game, $move, $markedCells));
    }
}