<?php

declare(strict_types=1);

namespace App\Tests\WxH;

use App\Entity\Game;
use App\Entity\Move;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class MoveColTest extends WebTestCase
{
    private $uri = '/game/move';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->c = $this->client->getContainer();
    }

    	public function testValid()
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

		$data = [Move::ROW => Move::MIN_INDEX, Move::COLUMN => Move::MIN_INDEX];
		$this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
		$this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
		$responseBody = json_decode($this->client->getResponse()->getContent(), true);
		$this->assertEquals($data[Move::COLUMN], $responseBody[Move::COLUMN] );
    	}

    public function testEmptyRequest()
    {
        $this->client->request('POST', $this->uri);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Move::ROW => Move::ERROR_ROW_MISSING]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testNotSet()
    {
        $data = [Move::ROW => Move::MIN_INDEX];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Move::COLUMN => Move::ERROR_COLUMN_MISSING]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testGameNotSet()
    {
        $data = [Move::ROW => Move::MIN_INDEX, Move::COLUMN => 'not-integer'];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Game::STATUS => Game::ERROR_CAN_NOT_FIND]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testInvalidType()
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

        $data = [Move::ROW => Move::MIN_INDEX, Move::COLUMN => 'not-integer'];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Move::MOVE => Move::ERROR_MOVE_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testInvalidType2()
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

        $data = [Move::ROW => Move::MIN_INDEX, Move::COLUMN => 3.9];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Move::MOVE => Move::ERROR_MOVE_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }

	public function testTooSmall()
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

		$data = [Move::ROW => Move::MIN_INDEX, Move::COLUMN => Move::MIN_INDEX - 1];
		$this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
		$this->assertEquals(['errors' => [Move::MOVE => Move::ERROR_MOVE_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
	}

	public function testTooBig()
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

		$data = [Move::ROW => Move::MIN_INDEX, Move::COLUMN => Game::MAX_HEIGHT_WIDTH + 1];
		$this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
		$this->assertEquals(['errors' => [Move::MOVE => Move::ERROR_MOVE_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
	}
}
