<?php

declare(strict_types=1);

namespace App\Tests\SelectedCell;

use App\Entity\Game;
use App\Entity\SelectedCell;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class Test extends WebTestCase
{
    private $uri = '/game/select_cell';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->c = $this->client->getContainer();
    }

    public function testValidSelectedCell()
    {
        $data = [Game::WIDTH => Game::MAX_HEIGHT_WIDTH, Game::HEIGHT => Game::MAX_HEIGHT_WIDTH, Game::SELECTED_CELL_CNT_TO_WIN => Game::MAX_HEIGHT_WIDTH];
        $this->client->request('POST', '/game', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = [SelectedCell::ROW => Game::MAX_HEIGHT_WIDTH - 1, SelectedCell::COLUMN => SelectedCell::MIN_INDEX];
        $this->client->request('POST', '/game/select_cell', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/game');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $selectedCells = $responseBody[Game::SELECTED_CELLS];
        $expected = [SelectedCell::IS_TIE => false, SelectedCell::IS_LAST => false, SelectedCell::SYMBOL => SelectedCell::SYMBOL_X, SelectedCell::ROW => $data[SelectedCell::ROW], SelectedCell::COLUMN => $data[SelectedCell::COLUMN]];
        $this->assertEquals($selectedCells[$data[SelectedCell::ROW]][$data[SelectedCell::COLUMN]], $expected);
        $this->assertEquals($responseBody[Game::NEXT_SYMBOL], SelectedCell::SYMBOL_O);
    }
}
