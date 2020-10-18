<?php

declare(strict_types=1);

namespace App\Tests\WxH;

use App\Entity\Game;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GridUnitTest extends KernelTestCase
{
    private $c;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->c = $kernel->getContainer();
    }

    public function testValidMinMaxDimension()
    {
        $min = 3;
        $max = 5;
        $game = new Game();
        $game->setStatus(Game::DRAFT);
        $game->setHeight($min);
        $game->setWidth($max);
        $this->assertEquals($min, $game->getMinDimension());
        $this->assertEquals($max, $game->getMaxDimension());
    }

    public function testValidMinMaxDimension2()
    {
        $min = 3;
        $max = 5;
        $game = new Game();
        $game->setStatus(Game::DRAFT);
        $game->setWidth($max);
        $game->setHeight($min);
        $this->assertEquals($min, $game->getMinDimension());
        $this->assertEquals($max, $game->getMaxDimension());
    }
}
