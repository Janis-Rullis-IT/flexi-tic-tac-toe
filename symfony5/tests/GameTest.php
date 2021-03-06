<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Game;
use App\Entity\SelectedCell;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GameTest extends WebTestCase
{
    private $uri = '/game';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->c = $this->client->getContainer();
    }

    public function testNotFound()
    {
        $this->client->request('GET', $this->uri);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Game::ID => Game::ERROR_CAN_NOT_FIND]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testFound()
    {
        $data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', '/game/board', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', $this->uri);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($data[Game::HEIGHT], $responseBody[Game::HEIGHT]);
        $this->assertTrue(isset($responseBody[Game::SELECTED_CELLS]));
        $this->assertEquals(SelectedCell::SYMBOL_X, $responseBody[Game::NEXT_SYMBOL]);
    }

    public function testValidMarkOngoing()
    {
        $data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', '/game/board', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = [Game::SELECTED_CELL_CNT_TO_WIN => Game::MAX_HEIGHT_WIDTH];
        $this->client->request('PUT', '/game/rules', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = [];
        $this->client->request('PUT', '/game/ongoing', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(Game::ONGOING, $responseBody[Game::STATUS]);
    }

    public function testValidStart()
    {
        $data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH, Game::SELECTED_CELL_CNT_TO_WIN => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', '/game', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', $this->uri);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertNotEmpty($responseBody[Game::ID]);
    }
}
