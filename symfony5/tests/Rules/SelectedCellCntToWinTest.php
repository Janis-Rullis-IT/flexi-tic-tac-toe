<?php

declare(strict_types=1);

namespace App\Tests\Rules;

use App\Entity\Game;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SelectedCellCntToWinTest extends WebTestCase
{
    private $uri = '/game/rules';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->c = $this->client->getContainer();
    }

    public function testWidthValid()
    {
        $data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', '/game/grid', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = [Game::SELECTED_CELL_CNT_TO_WIN => Game::MAX_HEIGHT_WIDTH];
        $this->client->request('PUT', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($data[Game::SELECTED_CELL_CNT_TO_WIN], $responseBody[Game::SELECTED_CELL_CNT_TO_WIN]);
    }

//    public function testHeightNotSet()
//    {
//        $data = [Game::WIDTH => Game::MIN_HEIGHT_WIDTH];
//        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
//        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
//        $this->assertEquals(['errors' => [Game::HEIGHT => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
//    }
//
//    public function testHeightInvalidType()
//    {
//        $data = [Game::WIDTH => 'string-not-an-int', Game::HEIGHT => Game::MIN_HEIGHT_WIDTH];
//        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
//        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
//        $this->assertEquals(['errors' => [Game::HEIGHT_WIDTH => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
//    }
//
//    public function testHeightInvalidType2()
//    {
//        $data = [Game::WIDTH => Game::MIN_HEIGHT_WIDTH, Game::HEIGHT => 1.9];
//        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
//        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
//        $this->assertEquals(['errors' => [Game::HEIGHT_WIDTH => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
//    }
//
//    public function testHeightTooSmall()
//    {
//        $data = [Game::WIDTH => Game::MIN_HEIGHT_WIDTH, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH - 1];
//        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
//        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
//        $this->assertEquals(['errors' => [Game::HEIGHT => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
//    }
//
//    public function testHeightTooBig()
//    {
//        $data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH, Game::HEIGHT => Game::MAX_HEIGHT_WIDTH + 1];
//        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
//        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
//        $this->assertEquals(['errors' => [Game::HEIGHT => Game::ERROR_HEIGHT_WIDTH_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
//    }
}
