<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Game;
use App\Interfaces\IGameRepo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GameUnitTest extends KernelTestCase
{
    private $c;
    private $gameRepo;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->c = $kernel->getContainer();
        $this->gameRepo = $this->c->get('test.'.IGameRepo::class);
    }

    public function testValid()
    {
        $this->assertNull($this->gameRepo->getCurrentDraft());

        $item = $this->gameRepo->insertDraftIfNotExist();
        $this->assertGreaterThan(1, $item->getId());
        $this->assertEquals(Game::DRAFT, $item->getStatus());
        $this->assertEquals(3, $item->getWidth());
        $this->assertEquals(3, $item->getHeight());

        $item2 = $this->gameRepo->getCurrentDraft();
        $this->assertEquals($item2->getId(), $item->getId());

        $item3 = $this->gameRepo->getCurrent();
        $this->assertEquals($item3->getId(), $item->getId());
    }
}
