<?php

namespace App\Interfaces;

use App\Entity\Game;
use App\Entity\Move;

interface IMoveRepo
{
    public function selectCell(Game $game, int $row, int $column): Move;

//    public function markAsCompleted(Order $order): Order;
//
//    public function mustFindUsersOrder(int $userId, int $orderId): Order;
//
//    public function mustFindUsersOrders(int $userId): array;
}
