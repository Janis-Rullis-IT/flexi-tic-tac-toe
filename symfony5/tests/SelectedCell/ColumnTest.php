<?php

declare(strict_types=1);

namespace App\Tests\SelectedCell;

use App\Entity\Game;
use App\Entity\SelectedCell;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ColumnTest extends WebTestCase
{
    private $uri = '/game/select_cell';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->c = $this->client->getContainer();
    }

    public function testValid()
    {
        $data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH, Game::SELECTED_CELL_CNT_TO_WIN => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', '/game', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = [SelectedCell::ROW => SelectedCell::MIN_INDEX, SelectedCell::COLUMN => SelectedCell::MIN_INDEX];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($data[SelectedCell::COLUMN], $responseBody[SelectedCell::COLUMN]);
    }

    public function testEmptyRequest()
    {
        $this->client->request('POST', $this->uri);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [SelectedCell::ROW => SelectedCell::ERROR_ROW_MISSING]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testNotSet()
    {
        $data = [SelectedCell::ROW => SelectedCell::MIN_INDEX];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [SelectedCell::COLUMN => SelectedCell::ERROR_COLUMN_MISSING]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testGameNotSet()
    {
        $data = [SelectedCell::ROW => SelectedCell::MIN_INDEX, SelectedCell::COLUMN => 'not-integer'];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [Game::STATUS => Game::ERROR_CAN_NOT_FIND]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testInvalidType()
    {
        $data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH, Game::SELECTED_CELL_CNT_TO_WIN => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', '/game', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = [SelectedCell::ROW => SelectedCell::MIN_INDEX, SelectedCell::COLUMN => 'not-integer'];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [SelectedCell::SELECTED_CELL => SelectedCell::ERROR_SELECTED_CELL_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testInvalidType2()
    {
        $data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH, Game::SELECTED_CELL_CNT_TO_WIN => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', '/game', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = [SelectedCell::ROW => SelectedCell::MIN_INDEX, SelectedCell::COLUMN => 3.9];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [SelectedCell::SELECTED_CELL => SelectedCell::ERROR_SELECTED_CELL_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testTooSmall()
    {
        $data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH, Game::SELECTED_CELL_CNT_TO_WIN => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', '/game', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = [SelectedCell::ROW => SelectedCell::MIN_INDEX, SelectedCell::COLUMN => SelectedCell::MIN_INDEX - 1];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [SelectedCell::SELECTED_CELL => SelectedCell::ERROR_SELECTED_CELL_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testTooBig()
    {
        $data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH, Game::HEIGHT => Game::MIN_HEIGHT_WIDTH, Game::SELECTED_CELL_CNT_TO_WIN => Game::MIN_HEIGHT_WIDTH];
        $this->client->request('POST', '/game', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = [SelectedCell::ROW => SelectedCell::MIN_INDEX, SelectedCell::COLUMN => Game::MAX_HEIGHT_WIDTH + 1];
        $this->client->request('POST', $this->uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['errors' => [SelectedCell::SELECTED_CELL => SelectedCell::ERROR_SELECTED_CELL_INVALID]], json_decode($this->client->getResponse()->getContent(), true));
    }
}
