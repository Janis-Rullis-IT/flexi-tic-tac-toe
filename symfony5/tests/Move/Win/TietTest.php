<?php

declare(strict_types=1);

namespace App\Tests\Move\Win;

use App\Service\MoveService;
use App\Entity\Move;
use App\Interfaces\IGameRepo;
use App\Interfaces\IMoveRepo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TieUnitTest extends KernelTestCase
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
		$this->moveService = $this->c->get('test.'.MoveService::class);
    }

    public function testValid()
    {
        $boardDimension = 3;
        $this->assertNull($this->gameRepo->getCurrentDraft());
        $game = $this->gameRepo->insertDraftIfNotExist();
        $game = $this->gameRepo->setBoardDimensions($game, $boardDimension, $boardDimension);
        $game = $this->gameRepo->setRules($game, $boardDimension);
        $game->setMoveCntToWin($boardDimension);
        $game = $this->gameRepo->markAsStarted($game);

        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX, Move::MIN_INDEX);
        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX, Move::MIN_INDEX + 1);
        $game = $this->gameRepo->toggleNextSymbol($game);
        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX, Move::MIN_INDEX + 1); // XX0

        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX + 1, Move::MIN_INDEX);
        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX + 1, Move::MIN_INDEX + 1);
        $game = $this->gameRepo->toggleNextSymbol($game);
        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX + 1, Move::MIN_INDEX + 2); // OOX

        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX + 2, Move::MIN_INDEX);
        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX + 2, Move::MIN_INDEX + 1);
        $game = $this->gameRepo->toggleNextSymbol($game);
        $move = $this->moveRepo->selectCell($game, Move::MIN_INDEX + 2, Move::MIN_INDEX + 2); // XXO

        $this->assertEquals($game->getTotalCellCnt(), $game->getHeight() * $game->getWidth());
        $this->assertTrue($this->moveService->isTie($game, $this->moveRepo->getTotalSelectedMoveCnt($game->getId())));
    }
}
