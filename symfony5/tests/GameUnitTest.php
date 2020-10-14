<?php

namespace App\Tests;

use App\Service\GameCreatorService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GameUnitTest extends KernelTestCase
{
    private $c;
    private $orderProductCreator;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->c = $kernel->getContainer();
        $this->orderShippingService = $this->c->get('test.'.GameCreatorService::class);
    }

    public function testInvalidCustomer()
    {
        $this->assertEquals(1, 1);
    }

//    public function testEnumExceptions()
//    {
//        $order = new Order();
//
//        $this->expectException(\InvalidArgumentException::class);
//        $this->expectExceptionMessage("'aaa' ".\App\Helper\EnumType::INVALID_ENUM_VALUE);
//        $this->expectExceptionCode(1);
//
//        $order->setIsDomestic('aaa');
//    }
}
