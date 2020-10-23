<?php

namespace App\Interfaces;

use App\Entity\Game;
use App\Entity\Move;

interface ISelectedCellRepo
{
    public function select(Game $game, int $row, int $column): Move;

    public function getTotalCnt(int $gameId): int;

    public function getAll(int $gameId, string $symbol, $organizeInRowColumns = true): array;

    public function getFromRow(int $gameId, string $symbol, int $rowNumber): array;

    public function getFromColumn(int $gameId, string $symbol, int $columnNumber): array;

    public function getOrderedByRows(int $gameId, string $symbol): array;

    public function getOrderedByColumns(int $gameId, string $symbol): array;
}
