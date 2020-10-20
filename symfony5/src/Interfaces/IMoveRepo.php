<?php

namespace App\Interfaces;

use App\Entity\Game;
use App\Entity\Move;

interface IMoveRepo
{
    public function selectCell(Game $game, int $row, int $column): Move;

    public function getMarkedCells(int $gameId, string $symbol): array;
}
