<?php

namespace App\Tests;

use App\Service\GameCreatorService;
use App\Entity\Game;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * #12 /game/grid.
 */
class GameTest extends WebTestCase
{
    private $impossibleInt = 3147483648;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->c = $this->client->getContainer();
        $this->orderShippingService = $this->c->get('test.'.GameCreatorService::class);
    }

    public function testEmptyRequest()
    {
        $uri = '/game/grid';
        $this->client->request('POST', $uri);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals([Game::WIDTH => Game::WIDTH], json_decode($this->client->getResponse()->getContent(), true));
    }
}
