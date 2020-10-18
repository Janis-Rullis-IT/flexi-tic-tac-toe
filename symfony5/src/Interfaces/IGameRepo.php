<?php

namespace App\Interfaces;

use App\Entity\Game;

interface IGameRepo
{
    public function setBoardDimensions(Game $item, int $width, int $height): Game;

    public function setRules(Game $item, int $moveCntToWin): Game;

//    public function insertDraftIfNotExist(int $customerId): Order;
//
//    public function getCurrentDraft(int $customerId): ?Order;
//
//    public function setOrderCostsFromCartItems(Order $order): bool;
//
//    public function fillShipping(Order $order, array $shippingData): Order;
//
//    public function save();
//
//    public function markAsCompleted(Order $order): Order;
//
//    public function mustFindUsersOrder(int $userId, int $orderId): Order;
//
//    public function mustFindUsersOrders(int $userId): array;
}
