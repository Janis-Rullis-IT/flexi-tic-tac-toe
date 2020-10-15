<?php
declare(strict_types=1);
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
		$this->orderShippingService = $this->c->get('test.' . GameCreatorService::class);
	}

//	public function testEmptyRequest()
//	{
//		$uri = '/game/grid';
//		$this->client->request('POST', $uri);
//		$this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
//		$this->assertEquals([Game::WIDTH => Game::ERROR_WIDTH_INVALID], json_decode($this->client->getResponse()->getContent(), true));
//	}
//
//	public function testWidthNotSet()
//	{
//		$uri = '/game/grid';
//		$data = ['height' => 3];
//		$this->client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
//		$this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
//		$this->assertEquals([Game::WIDTH => Game::ERROR_WIDTH_INVALID], json_decode($this->client->getResponse()->getContent(), true));
//	}
	
	public function testWidthInvalidType()
	{
		$uri = '/game/grid';
		$data = ['width' => 'string-not-an-int', 'height' => Game::MIN_WIDTH];
		$this->client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
		$this->assertEquals([Game::WIDTH => Game::ERROR_WIDTH_INVALID], json_decode($this->client->getResponse()->getContent(), true));
	}
	
	public function testWidthInvalidType2()
	{
		$uri = '/game/grid';
		$data = ['width' => 1.9, 'height' => Game::MIN_WIDTH];
		$this->client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
		$this->assertEquals([Game::WIDTH => Game::ERROR_WIDTH_INVALID], json_decode($this->client->getResponse()->getContent(), true));
	}
	
	public function testWidthTooSmall()
	{
		$uri = '/game/grid';
		$data = ['width' => Game::MIN_WIDTH - 1, 'height' => Game::MIN_WIDTH];
		$this->client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
		$this->assertEquals([Game::WIDTH => Game::ERROR_WIDTH_INVALID], json_decode($this->client->getResponse()->getContent(), true));
	}
	
	public function testWidthTooBig()
	{
		$uri = '/game/grid';
		$data = ['width' => Game::MAX_WIDTH + 1, 'height' => 3];
		$this->client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
		$this->assertEquals([Game::WIDTH => Game::ERROR_WIDTH_INVALID], json_decode($this->client->getResponse()->getContent(), true));
	}
}
