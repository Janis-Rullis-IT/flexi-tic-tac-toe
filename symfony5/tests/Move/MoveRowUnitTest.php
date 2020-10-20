<?php

declare(strict_types=1);

namespace App\Tests\Move;

use App\Entity\Game;
use App\Entity\Move;
use App\Exception\MoveValidatorException;
use App\Interfaces\IGameRepo;
use App\Interfaces\IMoveRepo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MoveRowUnitTest extends KernelTestCase
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
        $move = new Move();
        $game = new Game();
        $game->setStatus(Game::DRAFT);
        $game->setHeight(Game::MAX_HEIGHT_WIDTH);
        $game->setWidth(Game::MAX_HEIGHT_WIDTH);
        $game->setMoveCntToWin(Game::MAX_HEIGHT_WIDTH);

        $game->setStatus(Game::ONGOING);
        $move->setRow($game, $move->getMaxAllowedRow($game));
        $this->assertEquals($move->getRow(), $move->getMaxAllowedRow($game));
    }

    public function testNotGame()
    {
        $move = new Move();
        $this->expectException(\TypeError::class);
        $move->setRow('a', 1);
    }

    public function testNotInteger()
    {
        $move = new Move();
        $this->expectException(\TypeError::class);
        $move->setRow(new Game(), 'a');
    }

    public function testNotInteger2()
    {
        $move = new Move();
        $this->expectException(\TypeError::class);
        $move->setRow(new Game(), 3.9);
    }

    public function testStatusNotSet()
    {
        $move = new Move();

        $this->expectException(MoveValidatorException::class);
        $this->expectExceptionCode(Move::ERROR_MOVE_ONLY_FOR_ONGOING_CODE, Move::ERROR_MOVE_ONLY_FOR_ONGOING);
        $move->setRow(new Game(), Move::MIN_INDEX);
    }

    public function testInvalidStatusSet()
    {
        $move = new Move();
        $game = new Game();
        $game->setStatus(Game::DRAFT);

        $this->expectException(MoveValidatorException::class);
        $this->expectExceptionCode(Move::ERROR_MOVE_ONLY_FOR_ONGOING_CODE, Move::ERROR_MOVE_ONLY_FOR_ONGOING);
        $move->setRow($game, Move::MIN_INDEX);
    }

    public function testTooSmall()
    {
        $move = new Move();
        $game = new Game();
        $game->setStatus(Game::DRAFT);
        $game->setHeight(Game::MAX_HEIGHT_WIDTH);
        $game->setWidth(Game::MAX_HEIGHT_WIDTH);
        $game->setMoveCntToWin(Game::MAX_HEIGHT_WIDTH);

        $game->setStatus(Game::ONGOING);

        $this->expectException(MoveValidatorException::class);
        $this->expectExceptionCode(Move::ERROR_MOVE_INVALID_CODE, Move::ERROR_MOVE_INVALID);
        $move->setRow($game, Move::MIN_INDEX - 1);
    }

    public function testTooBig()
    {
        $move = new Move();
        $game = new Game();
        $game->setStatus(Game::DRAFT);
        $game->setHeight(Game::MAX_HEIGHT_WIDTH);
        $game->setWidth(Game::MAX_HEIGHT_WIDTH);
        $game->setMoveCntToWin(Game::MAX_HEIGHT_WIDTH);

        $game->setStatus(Game::ONGOING);

        $this->expectException(MoveValidatorException::class);
        $this->expectExceptionCode(Move::ERROR_MOVE_INVALID_CODE, Move::ERROR_MOVE_INVALID);
        $move->setRow($game, $move->getMaxAllowedRow($game) + 1);
    }
}