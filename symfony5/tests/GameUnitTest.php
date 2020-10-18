<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Game;
use App\Interfaces\IGameRepo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GameUnitTest extends KernelTestCase
{
    private $c;
	private $gameRepo ;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();	
        $this->c = $kernel->getContainer();
		$this->gameRepo = $this->c->get('test.'.IGameRepo::class);
    }

    public function testValid()
    {
		$this->assertNull($this->gameRepo->getCurrentDraft());
    }
}
