<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Entity\SelectedCell;
use App\Exception\SelectedCellValidatorException;
use App\Interfaces\IGameRepo;
use App\Interfaces\ISelectedCellRepo;

final class SelectedCellService
{
    private $gameRepo;
    private $selectedCellRepo;

    public function __construct(IGameRepo $gameRepo, ISelectedCellRepo $selectedCellRepo)
    {
        $this->gameRepo = $gameRepo;
        $this->selectedCellRepo = $selectedCellRepo;
    }

    /**
     * #17 Select the cell.
     *
     * @param array $request
     *
     * @throws \App\Exception\SelectedCellValidatorException
     */
    public function select(?array $request): SelectedCell
    {
        if (!isset($request[SelectedCell::ROW])) {
            throw new SelectedCellValidatorException([SelectedCell::ROW => SelectedCell::ERROR_ROW_MISSING], SelectedCell::ERROR_ROW_MISSING_CODE);
        }
        if (!isset($request[SelectedCell::COLUMN])) {
            throw new SelectedCellValidatorException([SelectedCell::COLUMN => SelectedCell::ERROR_COLUMN_MISSING], SelectedCell::ERROR_COLUMN_MISSING_CODE);
        }
        try {
            $game = $this->gameRepo->mustFindCurrentOngoing();
            $SelectedCell = $this->selectedCellRepo->select($game, $request[SelectedCell::ROW], $request[SelectedCell::COLUMN]);
            $game = $this->gameRepo->toggleNextSymbol($game);
            $totalSelectedSelectedCellCnt = $this->selectedCellRepo->getTotalCnt($game->getId());

            if ($this->isWin($totalSelectedSelectedCellCnt, $game, $SelectedCell)) {
                $this->gameRepo->markAsCompleted($game);
                $SelectedCell->setIsLast(true);
            } elseif ($this->isTie($game, $totalSelectedSelectedCellCnt)) {
                $this->gameRepo->markAsCompleted($game);
                $SelectedCell->setIsLast(true);
                $SelectedCell->setIsTie(true);
            }

            return $SelectedCell;
        } catch (\Error $ex) {
            throw new SelectedCellValidatorException([SelectedCell::SELECTED_CELL => SelectedCell::ERROR_SELECTED_CELL_INVALID], SelectedCell::ERROR_SELECTED_CELL_INVALID_CODE);
        }
    }

    /**
     * #19 Check if there's enough marked cells side-by-side in columns, rows and diagonals with the same symbol.
     */
    public function isWin(int $totalSelectedSelectedCellCnt, Game $game, SelectedCell $SelectedCell): bool
    {
        // #19 Don't continue if there is not enough marked cells.
        if ($totalSelectedSelectedCellCnt < $game->getSelectedCellCntToWin()) {
            return false;
        }

        if ($this->isRowWin($totalSelectedSelectedCellCnt, $game, $SelectedCell, $this->selectedCellRepo->getFromRow($game->getId(), $SelectedCell->getSymbol(), $SelectedCell->getRow()))) {
            return true;
        }
        if ($this->isColumnWin($totalSelectedSelectedCellCnt, $game, $SelectedCell, $this->selectedCellRepo->getFromColumn($game->getId(), $SelectedCell->getSymbol(), $SelectedCell->getColumn()))) {
            return true;
        }
        if ($this->isDiagonalWin($totalSelectedSelectedCellCnt, $game, $SelectedCell, $this->selectedCellRepo->getAll($game->getId(), $SelectedCell->getSymbol(), $SelectedCell->getColumn()))) {
            return true;
        }

        return false;
    }

    /**
     * #19 Check if there's enough marked cells diagonally with the same symbol.
     *
     * @param array $cells
     */
    public function isDiagonalWin(int $totalSelectedSelectedCellCnt, Game $game, SelectedCell $SelectedCell, ?array $cells = []): bool
    {
        if ($game->getSelectedCellCntToWin() === $this->getMarkedCellCntDiagonallyFromLeftToRight($totalSelectedSelectedCellCnt, $game, $SelectedCell, $cells)) {
            return true;
        }
        if ($game->getSelectedCellCntToWin() === $this->getMarkedCellCntDiagonallyFromRightToLeft($totalSelectedSelectedCellCnt, $game, $SelectedCell, $cells)) {
            return true;
        }

        return false;
    }

    /**
     * #19 Check if there's enough marked cells side-by-side in the row with the same symbol.
     *
     * @param array $cells
     */
    public function isRowWin(int $totalSelectedSelectedCellCnt, Game $game, SelectedCell $SelectedCell, ?array $cells = []): bool
    {
        $hasWin = false;
        $markedCellCntInRow = 1;

        // #19 Check that the row contains enough selected cells to have a win.
        if ($totalSelectedSelectedCellCnt < $game->getSelectedCellCntToWin()) {
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
    public function isColumnWin(int $totalSelectedSelectedCellCnt, Game $game, SelectedCell $SelectedCell, ?array $cells = []): bool
    {
        $hasWin = false;
        $markedCellCntInColumn = 1;

        // #19 Check that the column contains enough selected cells to have a win.
        if ($totalSelectedSelectedCellCnt < $game->getSelectedCellCntToWin()) {
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

    public function getMarkedCellCntDiagonallyFromLeftToRight(int $totalSelectedSelectedCellCnt, Game $game, SelectedCell $SelectedCell, ?array $cells = []): int
    {
        $cntInNorthWest = $this->getMarkedCellCntNorthWest($totalSelectedSelectedCellCnt, $game, $SelectedCell, $cells);
        $startingCell = 1;
        $cntInSouthEast = $this->getMarkedCellCntSouthEast($totalSelectedSelectedCellCnt, $game, $SelectedCell, $cells);

        return $cntInNorthWest + $startingCell + $cntInSouthEast;
    }

    public function getMarkedCellCntDiagonallyFromRightToLeft(int $totalSelectedSelectedCellCnt, Game $game, SelectedCell $SelectedCell, ?array $cells = []): int
    {
        $cntInNorthWest = $this->getMarkedCellCntNorthEast($totalSelectedSelectedCellCnt, $game, $SelectedCell, $cells);
        $startingCell = 1;
        $cntInSouthEast = $this->getMarkedCellCntSouthWest($totalSelectedSelectedCellCnt, $game, $SelectedCell, $cells);

        return $cntInNorthWest + $startingCell + $cntInSouthEast;
    }

    public function getMarkedCellCntSouthWest(int $totalSelectedSelectedCellCnt, Game $game, SelectedCell $SelectedCell, ?array $cells = []): int
    {
        $markedCellCntInDiagonal = -1;

        // #19 Check that the column contains enough selected cells to have a win.
        if ($totalSelectedSelectedCellCnt < $game->getSelectedCellCntToWin()) {
            return 0;
        }

        // #19 Traverse cells diagonally till the end of the list has been reached.
        $continue = true;
        // #19 Start the traverse from the last selected cell.
        $row = $SelectedCell->getRow();
        $column = $SelectedCell->getColumn();

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

    public function getMarkedCellCntNorthEast(int $totalSelectedSelectedCellCnt, Game $game, SelectedCell $SelectedCell, ?array $cells = []): int
    {
        $markedCellCntInDiagonal = -1;

        // #19 Check that the column contains enough selected cells to have a win.
        if ($totalSelectedSelectedCellCnt < $game->getSelectedCellCntToWin()) {
            return 0;
        }

        // #19 Traverse cells dioganlly till the end of the list has been reached.
        $continue = true;
        // #19 Start the traverse from the last selected cell.
        $row = $SelectedCell->getRow();
        $column = $SelectedCell->getColumn();

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

    public function getMarkedCellCntSouthEast(int $totalSelectedSelectedCellCnt, Game $game, SelectedCell $SelectedCell, ?array $cells = []): int
    {
        $markedCellCntInDiagonal = -1;

        // #19 Check that the column contains enough selected cells to have a win.
        if ($totalSelectedSelectedCellCnt < $game->getSelectedCellCntToWin()) {
            return 0;
        }

        // #19 Traverse cells diagonally till the end of the list has been reached.
        $continue = true;
        // #19 Start the traverse from the last selected cell.
        $row = $SelectedCell->getRow();
        $column = $SelectedCell->getColumn();

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

    public function getMarkedCellCntNorthWest(int $totalSelectedSelectedCellCnt, Game $game, SelectedCell $SelectedCell, ?array $cells = []): int
    {
        $markedCellCntInDiagonal = -1;

        // #19 Check that the column contains enough selected cells to have a win.
        if ($totalSelectedSelectedCellCnt < $game->getSelectedCellCntToWin()) {
            return 0;
        }

        // #19 Traverse cells diagonally till the end of the list has been reached.
        $continue = true;
        // #19 Start the traverse from the last selected cell.
        $row = $SelectedCell->getRow();
        $column = $SelectedCell->getColumn();

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
    public function isTie(Game $game, int $totalSelectedSelectedCellCnt): bool
    {
        return $totalSelectedSelectedCellCnt === $game->getTotalCellCnt();
    }
}
