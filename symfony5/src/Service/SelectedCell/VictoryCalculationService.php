<?php

declare(strict_types=1);

namespace App\Service\SelectedCell;

use App\Entity\Game;
use App\Entity\SelectedCell;
use App\Interfaces\ISelectedCellRepo;
use App\Interfaces\IVictoryCalculationService;

final class VictoryCalculationService implements IVictoryCalculationService
{
    private $selectedCellRepo;

    public function __construct(ISelectedCellRepo $selectedCellRepo)
    {
        $this->selectedCellRepo = $selectedCellRepo;
    }

    /**
     * #19 Check if there's enough marked cells side-by-side in columns, rows and diagonals with the same symbol.
     */
    public function isWin(int $totalSelectedCellCnt, Game $game, SelectedCell $selectedCell): bool
    {
        // #19 Don't continue if there is not enough marked cells.
        if ($totalSelectedCellCnt < $game->getSelectedCellCntToWin()) {
            return false;
        }

        if ($this->isRowWin($totalSelectedCellCnt, $game, $selectedCell, $this->selectedCellRepo->getFromRow($game->getId(), $selectedCell->getSymbol(), $selectedCell->getRow()))) {
            return true;
        }
        if ($this->isColumnWin($totalSelectedCellCnt, $game, $selectedCell, $this->selectedCellRepo->getFromColumn($game->getId(), $selectedCell->getSymbol(), $selectedCell->getColumn()))) {
            return true;
        }
        if ($this->isDiagonalWin($totalSelectedCellCnt, $game, $selectedCell, $this->selectedCellRepo->getAll($game->getId(), $selectedCell->getSymbol(), $selectedCell->getColumn()))) {
            return true;
        }

        return false;
    }

    /**
     * #19 Check if there's enough marked cells diagonally with the same symbol.
     *
     * @param array $cells
     */
    public function isDiagonalWin(int $totalSelectedCellCnt, Game $game, SelectedCell $selectedCell, ?array $cells = []): bool
    {
        if ($game->getSelectedCellCntToWin() === $this->getSelectedCellCntDiagonallyFromLeftToRight($totalSelectedCellCnt, $game, $selectedCell, $cells)) {
            return true;
        }
        if ($game->getSelectedCellCntToWin() === $this->getSelectedCellCntDiagonallyFromRightToLeft($totalSelectedCellCnt, $game, $selectedCell, $cells)) {
            return true;
        }

        return false;
    }

    /**
     * #19 Check if there's enough marked cells side-by-side in the row with the same symbol.
     *
     * @param array $cells
     */
    public function isRowWin(int $totalSelectedCellCnt, Game $game, SelectedCell $selectedCell, ?array $cells = []): bool
    {
        $hasWin = false;
        $markedCellCntInRow = 1;

        // #19 Check that the row contains enough selected cells to have a win.
        if ($totalSelectedCellCnt < $game->getSelectedCellCntToWin()) {
            return $hasWin;
        }

        // #19 Cells are ordered from left column to right, so every next item has a greater index.
        foreach ($cells as $i => $cell) {
            // #19 Is there a marked cell on the right?
            if (isset($cells[$i + 1])) {
                ++$markedCellCntInRow;

                // #19 Stop because enough marked cells are collected.
                if ($markedCellCntInRow === $game->getSelectedCellCntToWin()) {
                    $hasWin = true;
                    break;
                }
            }
        }

        return $hasWin;
    }

    /**
     * #19 Check if there's enough marked cells side-by-side in the column with the same symbol.
     *
     * @param array $cells
     */
    public function isColumnWin(int $totalSelectedCellCnt, Game $game, SelectedCell $selectedCell, ?array $cells = []): bool
    {
        $hasWin = false;
        $markedCellCntInColumn = 1;

        // #19 Check that the column contains enough selected cells to have a win.
        if ($totalSelectedCellCnt < $game->getSelectedCellCntToWin()) {
            return $hasWin;
        }

        // #19 Cells are ordered from top to bottom, so every next item has a greater index.
        foreach ($cells as $i => $cell) {
            // #19 Is there a marked cell on the right?
            if (isset($cells[$i + 1])) {
                ++$markedCellCntInColumn;

                // #19 Stop because enough marked cells are collected.
                if ($markedCellCntInColumn === $game->getSelectedCellCntToWin()) {
                    $hasWin = true;
                    break;
                }
            }
        }

        return $hasWin;
    }

    public function getSelectedCellCntDiagonallyFromLeftToRight(int $totalSelectedCellCnt, Game $game, SelectedCell $selectedCell, ?array $cells = []): int
    {
        $cntInNorthWest = $this->getSelectedCellCntNorthWest($totalSelectedCellCnt, $game, $selectedCell, $cells);
        $startingCell = 1;
        $cntInSouthEast = $this->getSelectedCellCntSouthEast($totalSelectedCellCnt, $game, $selectedCell, $cells);

        return $cntInNorthWest + $startingCell + $cntInSouthEast;
    }

    public function getSelectedCellCntDiagonallyFromRightToLeft(int $totalSelectedCellCnt, Game $game, SelectedCell $selectedCell, ?array $cells = []): int
    {
        $cntInNorthWest = $this->getSelectedCellCntNorthEast($totalSelectedCellCnt, $game, $selectedCell, $cells);
        $startingCell = 1;
        $cntInSouthEast = $this->getSelectedCellCntSouthWest($totalSelectedCellCnt, $game, $selectedCell, $cells);

        return $cntInNorthWest + $startingCell + $cntInSouthEast;
    }

    public function getSelectedCellCntSouthWest(int $totalSelectedCellCnt, Game $game, SelectedCell $selectedCell, ?array $cells = []): int
    {
        $markedCellCntInDiagonal = -1;

        // #19 Check that the column contains enough selected cells to have a win.
        if ($totalSelectedCellCnt < $game->getSelectedCellCntToWin()) {
            return 0;
        }

        // #19 Traverse cells diagonally till the end of the list has been reached.
        $continue = true;
        // #19 Start the traverse from the last selected cell.
        $row = $selectedCell->getRow();
        $column = $selectedCell->getColumn();

        while (true == $continue) {
            // #19 Is there a marked cell?
            if (isset($cells[$row][$column])) {
                ++$markedCellCntInDiagonal;

                // #19 SelectedCell diagonally.
                ++$row;
                --$column;
            } else {
                $continue = false;
            }
        }

        return $markedCellCntInDiagonal;
    }

    public function getSelectedCellCntNorthEast(int $totalSelectedCellCnt, Game $game, SelectedCell $selectedCell, ?array $cells = []): int
    {
        $markedCellCntInDiagonal = -1;

        // #19 Check that the column contains enough selected cells to have a win.
        if ($totalSelectedCellCnt < $game->getSelectedCellCntToWin()) {
            return 0;
        }

        // #19 Traverse cells dioganlly till the end of the list has been reached.
        $continue = true;
        // #19 Start the traverse from the last selected cell.
        $row = $selectedCell->getRow();
        $column = $selectedCell->getColumn();

        while (true == $continue) {
            // #19 Is there a marked cell?
            if (isset($cells[$row][$column])) {
                ++$markedCellCntInDiagonal;

                // #19 SelectedCell diagonally.
                --$row;
                ++$column;
            } else {
                $continue = false;
            }
        }

        return $markedCellCntInDiagonal;
    }

    public function getSelectedCellCntSouthEast(int $totalSelectedCellCnt, Game $game, SelectedCell $selectedCell, ?array $cells = []): int
    {
        $markedCellCntInDiagonal = -1;

        // #19 Check that the column contains enough selected cells to have a win.
        if ($totalSelectedCellCnt < $game->getSelectedCellCntToWin()) {
            return 0;
        }

        // #19 Traverse cells diagonally till the end of the list has been reached.
        $continue = true;
        // #19 Start the traverse from the last selected cell.
        $row = $selectedCell->getRow();
        $column = $selectedCell->getColumn();

        while (true == $continue) {
            // #19 Is there a marked cell?
            if (isset($cells[$row][$column])) {
                ++$markedCellCntInDiagonal;

                // #19 SelectedCell diagonally.
                ++$row;
                ++$column;
            } else {
                $continue = false;
            }
        }

        return $markedCellCntInDiagonal;
    }

    public function getSelectedCellCntNorthWest(int $totalSelectedCellCnt, Game $game, SelectedCell $selectedCell, ?array $cells = []): int
    {
        $markedCellCntInDiagonal = -1;

        // #19 Check that the column contains enough selected cells to have a win.
        if ($totalSelectedCellCnt < $game->getSelectedCellCntToWin()) {
            return 0;
        }

        // #19 Traverse cells diagonally till the end of the list has been reached.
        $continue = true;
        // #19 Start the traverse from the last selected cell.
        $row = $selectedCell->getRow();
        $column = $selectedCell->getColumn();

        while (true == $continue) {
            // #19 Is there a marked cell?
            if (isset($cells[$row][$column])) {
                ++$markedCellCntInDiagonal;

                // #19 SelectedCell diagonally.
                --$row;
                --$column;
            } else {
                $continue = false;
            }
        }

        return $markedCellCntInDiagonal;
    }

    /**
     * #37 If there's no winner and all cells are selected then it's a tie.
     */
    public function isTie(Game $game, int $totalSelectedCellCnt): bool
    {
        return $totalSelectedCellCnt === $game->getTotalCellCnt();
    }
}
