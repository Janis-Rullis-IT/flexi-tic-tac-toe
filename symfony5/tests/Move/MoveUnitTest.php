<?php

declare(strict_types=1);

namespace App\Tests\Move;

use App\Entity\Game;
use App\Entity\Move;
use App\Exception\MoveValidatorException;
use App\Interfaces\IGameRepo;
use App\Interfaces\ISelectedCellRepo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MoveUnitTest extends KernelTestCase
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
        $game->setMoveCntToWin(Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->markAsStarted($game);
        $move = $this->selectedCellRepo->select($game, Move::MIN_INDEX, Move::MIN_INDEX);
        $move = $this->selectedCellRepo->select($game, Move::MIN_INDEX, Move::MIN_INDEX + 1);
        $move = $this->selectedCellRepo->select($game, Move::MIN_INDEX + 1, Move::MIN_INDEX);

        $cells = $this->selectedCellRepo->getOrderedByRows($game->getId(), Move::SYMBOL_X);
        $this->assertEquals($cells[0][Move::ROW], Move::MIN_INDEX);
        $this->assertEquals($cells[1][Move::ROW], Move::MIN_INDEX);
        $this->assertEquals($cells[0][Move::COLUMN], Move::MIN_INDEX);
        $this->assertEquals($cells[1][Move::COLUMN], Move::MIN_INDEX + 1);

        $markedCells = $this->selectedCellRepo->getAll($game->getId(), Move::SYMBOL_X);
        $this->assertTrue(isset($markedCells[Move::MIN_INDEX][Move::MIN_INDEX]));
        $this->assertTrue(isset($markedCells[Move::MIN_INDEX][Move::MIN_INDEX + 1]));
        $this->assertTrue(isset($markedCells[Move::MIN_INDEX + 1][Move::MIN_INDEX]));

        $this->assertEquals(3, $this->selectedCellRepo->getTotalCnt($game->getId()));
    }

    public function testValid()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH, Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH);
        $game->setMoveCntToWin(Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->markAsStarted($game);
        $move = $this->selectedCellRepo->select($game, Move::MIN_INDEX, Move::MIN_INDEX);
        $this->assertEquals($move->getSymbol(), Move::SYMBOL_X);
        $this->assertEquals(1, $this->selectedCellRepo->getTotalCnt($game->getId()));
    }

    public function testCellAlreadyTaken()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH, Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH);
        $game->setMoveCntToWin(Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->markAsStarted($game);
        $move = $this->selectedCellRepo->select($game, Move::MIN_INDEX, Move::MIN_INDEX);

        $this->expectException(MoveValidatorException::class);
        $this->expectExceptionCode(Move::ERROR_MOVE_ALREADY_TAKEN_INVALID_CODE, Move::ERROR_MOVE_ALREADY_TAKEN_INVALID);
        $move = $this->selectedCellRepo->select($game, Move::MIN_INDEX, Move::MIN_INDEX);
    }
}
