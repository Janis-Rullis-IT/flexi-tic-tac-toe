<?php

declare(strict_types=1);

namespace App\Tests\Move;

use App\Entity\Game;
use App\Entity\Move;
use App\Exception\MoveValidatorException;
use App\Interfaces\IGameRepo;
use App\Interfaces\IMoveRepo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MoveUnitTest extends KernelTestCase
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
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH, Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH);
        $game->setMoveCntToWin(Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->markAsStarted($game);
        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX, Move::MIN_INDEX);
    }

    public function testCellAlreadyTaken()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, Game::MIN_HEIGHT_WIDTH, Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->setRules($game, Game::MIN_HEIGHT_WIDTH);
        $game->setMoveCntToWin(Game::MIN_HEIGHT_WIDTH);
        $game = $this->gameRepo->markAsStarted($game);
        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX, Move::MIN_INDEX);

        $this->expectException(MoveValidatorException::class);
        $this->expectExceptionCode(Move::ERROR_MOVE_ALREADY_TAKEN_INVALID_CODE, Move::ERROR_MOVE_ALREADY_TAKEN_INVALID);
        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX, Move::MIN_INDEX);
    }
}
