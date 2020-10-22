<?php

namespace App\Interfaces;

use App\Entity\Game;
use App\Entity\Move;
use Doctrine\ORM\QueryBuilder;

interface IMoveRepo
{
    public function selectCell(Game $game, int $row, int $column): Move;

    public function getMarkedCellsQueryBuilder(int $gameId, string $symbol): QueryBuilder;
	
	public function getTotalSelectedMoveCnt(int $gameId): int;
	
	public function getMarkedCells(int $gameId, string $symbol, $organizeInRowColumns = true): array;
	
	public function getMarkedCellsInTheRow(int $gameId, string $symbol, int $rowNumber): array;
	
	public function getMarkedCellsInTheColumn(int $gameId, string $symbol, int $columnNumber): array;
	
	public function getMarkedCellsOrderedByRows(int $gameId, string $symbol): array;
	
	public function getMarkedCellsOrderedByColumns(int $gameId, string $symbol): array;
}
