<?php

declare(strict_types=1);

namespace App\Tests\WxH;

use App\Entity\Game;
use App\Service\GameCreatorService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * #12 /game/grid.
 */
class GridHeightTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->c = $this->client->getContainer();
        $this->orderShippingService = $this->c->get('test.'.GameCreatorService::class);
    }

    public function testWidthValid()
    {
        $uri = '/game/grid';
        $data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($data[Game::HEIGHT], $responseBody[Game::HEIGHT]);
    }

    public function testEmptyRequest()
    {
        $uri = '/game/grid';
        $this->client->request('POST', $uri);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Game::WIDTH => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testHeightNotSet()
    {
        $uri = '/game/grid';
        $data = [Game::WIDTH => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Game::HEIGHT => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testHeightInvalidType()
    {
        $uri = '/game/grid';
        $data = [Game::WIDTH => 'string-not-an-int', Game::HEIGHT => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Game::HEIGHT_WIDTH => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testHeightInvalidType2()
    {
        $uri = '/game/grid';
        $data = [Game::WIDTH => Game::MIN_HEIGHT_WIDTH, Game::HEIGHT => 1.9];
        $this->client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Game::HEIGHT_WIDTH => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testHeightTooSmall()
    {
        $uri = '/game/grid';
        $data = [Game::WIDTH => Game::MIN_HEIGHT_WIDTH, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH - 1];
        $this->client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Game::HEIGHT => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testHeightTooBig()
    {
        $uri = '/game/grid';
        $data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH, Game::HEIGHT => Game::MAX_HEIGHT_WIDTH + 1];
        $this->client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Game::HEIGHT => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }
}
