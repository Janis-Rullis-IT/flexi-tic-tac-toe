<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Move;
use App\Entity\Game;
use App\Exception\MoveValidatorException;
use App\Interfaces\IGameRepo;
use App\Interfaces\IMoveRepo;

class MoveService
{
    private $gameRepo;
    private $moveRepo;

    public function __construct(IGameRepo $gameRepo, IMoveRepo $moveRepo)
    {
        $this->gameRepo = $gameRepo;
        $this->moveRepo = $moveRepo;
    }

    /**
     * #17 Select the cell.
     *
     * @param array $request
     *
     * @throws \App\Exception\MoveValidatorException
     */
    public function selectCell(?array $request): Move
    {
        if (!isset($request[Move::ROW])) {
            throw new MoveValidatorException([Move::ROW => Move::ERROR_ROW_MISSING], Move::ERROR_ROW_MISSING_CODE);
        }
        if (!isset($request[Move::COLUMN])) {
            throw new MoveValidatorException([Move::COLUMN => Move::ERROR_COLUMN_MISSING], Move::ERROR_COLUMN_MISSING_CODE);
        }
        try {
            $game = $this->gameRepo->mustFindCurrentOngoing();
            $move = $this->moveRepo->selectCell($game, $request[Move::ROW], $request[Move::COLUMN]);
            $game = $this->gameRepo->toggleNextSymbol($game);
            $totalSelectedMoveCnt = $this->moveRepo->getTotalSelectedMoveCnt($game->getId());

            if ($this->isWin($totalSelectedMoveCnt, $game, $move)) {
                $this->gameRepo->markAsCompleted($game);
                $move->setIsLast(true);
            } elseif ($this->isTie($game, $totalSelectedMoveCnt)) {
                $this->gameRepo->markAsCompleted($game);
                $move->setIsLast(true);
                $move->setIsTie(true);
            }

            return $move;
        } catch (\Error $ex) {
            throw new MoveValidatorException([Move::MOVE => Move::ERROR_MOVE_INVALID], Move::ERROR_MOVE_INVALID_CODE);
        }
    }
	
	    /**
     * #19 Check if there's enough marked cells side-by-side in columns, rows and diagonals with the same symbol.
     */
    public function isWin(int $totalSelectedMoveCnt, Game $game, Move $move): bool
    {
        // #19 Don't continue if there is not enough marked cells.
        if ($totalSelectedMoveCnt < $game->getMoveCntToWin()) {
            return false;
        }

        if ($this->isRowWin($totalSelectedMoveCnt, $game, $move, $this->moveRepo->getMarkedCellsInTheRow($game->getId(), $move->getSymbol(), $move->getRow()))) {
            return true;
        }
        if ($this->isColumnWin($totalSelectedMoveCnt, $game, $move, $this->moveRepo->getMarkedCellsInTheColumn($game->getId(), $move->getSymbol(), $move->getColumn()))) {
            return true;
        }
        if ($this->isDiagonalWin($totalSelectedMoveCnt, $game, $move, $this->moveRepo->getMarkedCells($game->getId(), $move->getSymbol(), $move->getColumn()))) {
            return true;
        }

        return false;
    }

    /**
     * #19 Check if there's enough marked cells diagonally with the same symbol.
     *
     * @param array $cells
     */
    public function isDiagonalWin(int $totalSelectedMoveCnt, Game $game, Move $move, ?array $cells = []): bool
    {
        if ($game->getMoveCntToWin() === $this->getMarkedCellCntDiagonallyFromLeftToRight($totalSelectedMoveCnt, $game, $move, $cells)) {
            return true;
        }
        if ($game->getMoveCntToWin() === $this->getMarkedCellCntDiagonallyFromRightToLeft($totalSelectedMoveCnt, $game, $move, $cells)) {
            return true;
        }

        return false;
    }

    /**
     * #19 Check if there's enough marked cells side-by-side in the row with the same symbol.
     *
     * @param array $cells
     */
    public function isRowWin(int $totalSelectedMoveCnt, Game $game, Move $move, ?array $cells = []): bool
    {
        $hasWin = false;
        $markedCellCntInRow = 1;

        // #19 Check that the row contains enough selected cells to have a win.
        if ($totalSelectedMoveCnt < $game->getMoveCntToWin()) {
            return $hasWin;
        }

        // #19 Cells are ordered from left column to right, so every next item has a greater index.
        foreach ($cells as $i => $cell) {
            // #19 Is there a marked cell on the right?
            if (isset($cells[$i + 1])) {
                ++$markedCellCntInRow;

                // #19 Stop because enough marked cells are collected.
                if ($markedCellCntInRow === $game->getMoveCntToWin()) {
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
    public function isColumnWin(int $totalSelectedMoveCnt, Game $game, Move $move, ?array $cells = []): bool
    {
        $hasWin = false;
        $markedCellCntInColumn = 1;

        // #19 Check that the column contains enough selected cells to have a win.
        if ($totalSelectedMoveCnt < $game->getMoveCntToWin()) {
            return $hasWin;
        }

        // #19 Cells are ordered from top to bottom, so every next item has a greater index.
        foreach ($cells as $i => $cell) {
            // #19 Is there a marked cell on the right?
            if (isset($cells[$i + 1])) {
                ++$markedCellCntInColumn;

                // #19 Stop because enough marked cells are collected.
                if ($markedCellCntInColumn === $game->getMoveCntToWin()) {
                    $hasWin = true;
                    break;
                }
            }
        }

        return $hasWin;
    }

    public function getMarkedCellCntDiagonallyFromLeftToRight(int $totalSelectedMoveCnt, Game $game, Move $move, ?array $cells = []): int
    {
        $cntInNorthWest = $this->getMarkedCellCntNorthWest($totalSelectedMoveCnt, $game, $move, $cells);
        $startingCell = 1;
        $cntInSouthEast = $this->getMarkedCellCntSouthEast($totalSelectedMoveCnt, $game, $move, $cells);

        return $cntInNorthWest + $startingCell + $cntInSouthEast;
    }

    public function getMarkedCellCntDiagonallyFromRightToLeft(int $totalSelectedMoveCnt, Game $game, Move $move, ?array $cells = []): int
    {
        $cntInNorthWest = $this->getMarkedCellCntNorthEast($totalSelectedMoveCnt, $game, $move, $cells);
        $startingCell = 1;
        $cntInSouthEast = $this->getMarkedCellCntSouthWest($totalSelectedMoveCnt, $game, $move, $cells);

        return $cntInNorthWest + $startingCell + $cntInSouthEast;
    }

    public function getMarkedCellCntSouthWest(int $totalSelectedMoveCnt, Game $game, Move $move, ?array $cells = []): int
    {
        $markedCellCntInDiagonal = -1;

        // #19 Check that the column contains enough selected cells to have a win.
        if ($totalSelectedMoveCnt < $game->getMoveCntToWin()) {
            return 0;
        }

        // #19 Traverse cells diagonally till the end of the list has been reached.
        $continue = true;
        // #19 Start the traverse from the last selected cell.
        $row = $move->getRow();
        $column = $move->getColumn();

        while (true == $continue) {
            // #19 Is there a marked cell?
            if (isset($cells[$row][$column])) {
                ++$markedCellCntInDiagonal;

                // #19 Move diagonally.
                ++$row;
                --$column;
            } else {
                $continue = false;
            }
        }

        return $markedCellCntInDiagonal;
    }

    public function getMarkedCellCntNorthEast(int $totalSelectedMoveCnt, Game $game, Move $move, ?array $cells = []): int
    {
        $markedCellCntInDiagonal = -1;

        // #19 Check that the column contains enough selected cells to have a win.
        if ($totalSelectedMoveCnt < $game->getMoveCntToWin()) {
            return 0;
        }

        // #19 Traverse cells dioganlly till the end of the list has been reached.
        $continue = true;
        // #19 Start the traverse from the last selected cell.
        $row = $move->getRow();
        $column = $move->getColumn();

        while (true == $continue) {
            // #19 Is there a marked cell?
            if (isset($cells[$row][$column])) {
                ++$markedCellCntInDiagonal;

                // #19 Move diagonally.
                --$row;
                ++$column;
            } else {
                $continue = false;
            }
        }

        return $markedCellCntInDiagonal;
    }

    public function getMarkedCellCntSouthEast(int $totalSelectedMoveCnt, Game $game, Move $move, ?array $cells = []): int
    {
        $markedCellCntInDiagonal = -1;

        // #19 Check that the column contains enough selected cells to have a win.
        if ($totalSelectedMoveCnt < $game->getMoveCntToWin()) {
            return 0;
        }

        // #19 Traverse cells diagonally till the end of the list has been reached.
        $continue = true;
        // #19 Start the traverse from the last selected cell.
        $row = $move->getRow();
        $column = $move->getColumn();

        while (true == $continue) {
            // #19 Is there a marked cell?
            if (isset($cells[$row][$column])) {
                ++$markedCellCntInDiagonal;

                // #19 Move diagonally.
                ++$row;
                ++$column;
            } else {
                $continue = false;
            }
        }

        return $markedCellCntInDiagonal;
    }

    public function getMarkedCellCntNorthWest(int $totalSelectedMoveCnt, Game $game, Move $move, ?array $cells = []): int
    {
        $markedCellCntInDiagonal = -1;

        // #19 Check that the column contains enough selected cells to have a win.
        if ($totalSelectedMoveCnt < $game->getMoveCntToWin()) {
            return 0;
        }

        // #19 Traverse cells diagonally till the end of the list has been reached.
        $continue = true;
        // #19 Start the traverse from the last selected cell.
        $row = $move->getRow();
        $column = $move->getColumn();

        while (true == $continue) {
            // #19 Is there a marked cell?
            if (isset($cells[$row][$column])) {
                ++$markedCellCntInDiagonal;

                // #19 Move diagonally.
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
    public function isTie(Game $game, int $totalSelectedMoveCnt): bool
    {
        return $totalSelectedMoveCnt === $game->getTotalCellCnt();
    }
}
