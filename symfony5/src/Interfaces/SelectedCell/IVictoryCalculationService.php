<?php

namespace App\Interfaces\SelectedCell;

use App\Entity\Game;
use App\Entity\SelectedCell;

interface IVictoryCalculationService
{
    public function isWin(int $totalSelectedCellCnt, Game $game, SelectedCell $selectedCell): bool;

    public function isDiagonalWin(int $totalSelectedCellCnt, Game $game, SelectedCell $selectedCell, ?array $cells = []): bool;

    public function isRowWin(int $totalSelectedCellCnt, Game $game, SelectedCell $selectedCell, ?array $cells = []): bool;

    public function isColumnWin(int $totalSelectedCellCnt, Game $game, SelectedCell $selectedCell, ?array $cells = []): bool;

    public function isTie(Game $game, int $totalSelectedCellCnt): bool;
}
