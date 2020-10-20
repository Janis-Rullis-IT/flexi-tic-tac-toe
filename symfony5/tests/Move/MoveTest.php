<?php

declare(strict_types=1);

namespace App\Tests\WxH;

use App\Entity\Game;
use App\Entity\Move;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class MoveTest extends WebTestCase
{
    private $uri = '/game/move';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->c = $this->client->getContainer();
    }

    public function testValidMove()
    {
        $data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH, Game::HEIGHT => Game::MAX_HEIGHT_WIDTH, Game::MOVE_CNT_TO_WIN => Game::MAX_HEIGHT_WIDTH];
        $this->client->request('POST', '/game', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = [Move::ROW => Game::MAX_HEIGHT_WIDTH - 1, Move::COLUMN => Move::MIN_INDEX];
        $this->client->request('POST', '/game/move', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/game');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $moves = $responseBody[Game::MOVES];
        $this->assertEquals($moves[$data[Move::ROW]][$data[Move::COLUMN]], [Move::SYMBOL => MOVE::SYMBOL_X, Move::ROW => $data[Move::ROW], Move::COLUMN => $data[Move::COLUMN]]);
        $this->assertEquals($responseBody[Game::NEXT_SYMBOL], Move::SYMBOL_O);
    }
}
