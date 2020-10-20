<?php

namespace App\Interfaces;

use App\Entity\Game;
use App\Entity\Move;
use Doctrine\ORM\QueryBuilder;

interface IMoveRepo
{
    public function selectCell(Game $game, int $row, int $column): Move;

    public function getMarkedCellsQueryBuilder(int $gameId, string $symbol): QueryBuilder;
}
