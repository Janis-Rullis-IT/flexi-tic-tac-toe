<?php

declare(strict_types=1);

namespace App\Tests\Board;

use App\Entity\Game;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WidthTest extends WebTestCase
{
    private $uri = '/game/board';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->c = $this->client->getContainer();
    }

    public function testWidthValid()
    {
        $data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        //		print_r($this->client->getResponse());exit;
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($data[Game::WIDTH], $responseBody[Game::WIDTH]);
    }

    public function testEmptyRequest()
    {
        $this->client->request('POST', $this->uri);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Game::WIDTH => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testWidthNotSet()
    {
        $data = [Game::HEIGHT => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Game::WIDTH => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testWidthInvalidType()
    {
        $data = [Game::WIDTH => 'string-not-an-int', Game::HEIGHT => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Game::HEIGHT_WIDTH => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testWidthInvalidType2()
    {
        $data = [Game::WIDTH => 1.9, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Game::HEIGHT_WIDTH => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testWidthTooSmall()
    {
        $data = [Game::WIDTH => Game::MIN_HEIGHT_WIDTH - 1, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Game::WIDTH => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testWidthTooBig()
    {
        $data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH + 1, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Game::WIDTH => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }
}
