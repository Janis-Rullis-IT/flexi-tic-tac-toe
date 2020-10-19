<?php

declare(strict_types=1);

namespace App\Tests\WxH;

use App\Entity\Game;
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
        $this->client->request('POST', '/game/grid', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', $this->uri);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($data[Game::HEIGHT], $responseBody[Game::HEIGHT]);
    }
	
	public function testValidMarkOngoing()
    {
		$data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', '/game/grid', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
		
		$data = [Game::MOVE_CNT_TO_WIN => Game::MAX_HEIGHT_WIDTH];
		$this->client->request('PUT', '/game/rules', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
		$this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
		
        $data = [];
        $this->client->request('PUT', '/game/ongoing', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(Game::ONGOING, $responseBody[Game::STATUS]);
    }
}
