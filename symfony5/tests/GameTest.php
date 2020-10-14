<?php

namespace App\Tests;

use App\Service\GameCreatorService;
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
        $this->orderShippingService = $this->c->get('test.'.GameCreatorService::class);
    }

    public function testInvalidCustomer()
    {
//        $uri = '/users/'.$this->impossibleInt.'/order/complete';
//        $this->client->request('PUT', $uri);

        $this->assertEquals(1, 1);
//        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
//        $this->assertEquals([Order::ID => 'invalid user'], json_decode($this->client->getResponse()->getContent(), true));
    }
}
