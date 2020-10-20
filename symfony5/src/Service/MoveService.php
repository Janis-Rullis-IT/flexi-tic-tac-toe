<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Entity\Move;
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

            // #16 TODO: This works. Integrate.
            //			$is = $this->isRowWin($game, $this->moveRepo->getMarkedCells($game->getId(), $move->getSymbol()));

            return $move;
        } catch (\Error $ex) {
            throw new MoveValidatorException([Move::MOVE => Move::ERROR_MOVE_INVALID], Move::ERROR_MOVE_INVALID_CODE);
        }
    }

    /**
     * #19 Check if there's enough marked cells side-by-side in the row with the same symbol.
     *
     * @param array $cells
     *
     * @return bool
     */
    public function isRowWin(Game $game, ?array $cells = [])
    {
        // #19 TODO: Most probably the Array needs to be replaced with a Collection.
        $cellCnt = count($cells);
        $hasWin = false;
        $markedCellCntInRow = 1;

        // #19 Don't continue if there is not enough marked cells.
        // #19 TODO: Don't return the list from DB if there is not enough marked cells.
        if ($cellCnt < $game->getMoveCntToWin()) {
            return $hasWin;
        }

        for ($i = 0; $i < $cellCnt - 1; ++$i) {
            // #19 Still on the same row?
            if ($cells[$i][Move::ROW] === $cells[$i + 1][Move::ROW]) {
                // #19 Check that the next marked cell is exactly 1 column on the right.
                if ($cells[$i][Move::COLUMN] === $cells[$i + 1][Move::COLUMN] - 1) {
                    ++$markedCellCntInRow;

                    // #19 Stop because enough marked cells are collected.
                    if ($markedCellCntInRow === $game->getMoveCntToWin()) {
                        $hasWin = true;
                        break;
                    }
                }
            }
            // #19 Reset the marked cell counter because the row has changed.
            else {
                $markedCellCntInRow = 1;
            }
        }

        return $hasWin;
    }
}
