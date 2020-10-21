<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Game;
use App\Entity\Move;
use App\Exception\MoveValidatorException;
use App\Interfaces\IMoveRepo;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * #12 Repo best practices:
 * - https://www.thinktocode.com/2018/03/05/repository-pattern-symfony/.
 * - https://www.thinktocode.com/2019/01/24/doctrine-repositories-should-be-collections-without-flush/.
 */
final class MoveRepository extends BaseRepository implements IMoveRepo
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, Move::class);
    }

    /**
     * #12 Set game board dimensions but don't store it yet.
     * Validations happen in those Entity methods.
     */
    public function selectCell(Game $game, int $row, int $column): Move
    {
        try {
            $item = new Move();
            $item->setGameId($game->getId());
            $item->setRow($game, $row);
            $item->setColumn($game, $column);
            $item->setSymbol($game->getNextSymbol());
            $this->em->persist($item);
            $this->em->flush();

            return $item;
        }
        // #18 Look in DB if such move has already been registered.
        catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            throw new MoveValidatorException([Move::MOVE => Move::ERROR_MOVE_ALREADY_TAKEN_INVALID], Move::ERROR_MOVE_ALREADY_TAKEN_INVALID_CODE);
        }
    }

    public function save()
    {
        $this->em->flush();
        $this->em->clear();
    }

    /**
     * #19 Collect marked cells by a specific symbol. Will be used to calc. the winner.
     *
     * @return \App\Repository\QueryBuilder
     */
    public function getMarkedCellsQueryBuilder(int $gameId, string $symbol): QueryBuilder
    {
        return $this->em->createQueryBuilder('a')
            ->select('a.row', 'a.column')
            ->from(Move::class, 'a')
            ->where('a.gameId = :gameId')
            ->andWhere('a.symbol = :symbol')
            ->setParameter('gameId', $gameId)
            ->setParameter('symbol', $symbol);
    }

    /**
     * #19 Collect marked cells by a specific symbol organized in [ROW][COLUMN]. Will be used to calc. the winner.
     *
     * @param type $organizeInRowColumns
     */
    public function getMarkedCells(int $gameId, string $symbol, $organizeInRowColumns = true): array
    {
        $q = $this->getMarkedCellsQueryBuilder($gameId, $symbol)->getQuery();
        $cells = $q->getResult();

        if ($organizeInRowColumns && !empty($cells)) {
            $organized = [];

            foreach ($cells as $cell) {
                $organized[$cell[Move::ROW]][$cell[Move::COLUMN]] = $cell;
            }

            return $organized;
        }

        return $cells;
    }

    /**
     * #19 Collect marked cells by a specific symbol in the specific row (last move). Will be used to calc. the winner.
     */
    public function getMarkedCellsInTheRow(int $gameId, string $symbol, int $rowNumber): array
    {
        $q = $this->getMarkedCellsQueryBuilder($gameId, $symbol)
            ->andWhere('a.row = :rowNumber')
            ->orderBy('a.row', 'ASC')
            ->addOrderBy('a.column', 'ASC')
            ->setParameter('rowNumber', $rowNumber)
            ->getQuery();
        $cells = $q->getResult();

        if (!empty($cells)) {
            $columns = [];

            foreach ($cells as $cell) {
                $columns[$cell[Move::COLUMN]] = $cell;
            }

            return $columns;
        }

        return $cells;
    }

    /**
     * #19 Collect marked cells by a specific symbol in the specific column (last move). Will be used to calc. the winner.
     */
    public function getMarkedCellsInTheColumn(int $gameId, string $symbol, int $columnNumber): array
    {
        $q = $this->getMarkedCellsQueryBuilder($gameId, $symbol)
            ->andWhere('a.column = :columnNumber')
            ->orderBy('a.column', 'ASC')
            ->addOrderBy('a.row', 'ASC')
            ->setParameter('columnNumber', $columnNumber)
            ->getQuery();
        $cells = $q->getResult();

        if (!empty($cells)) {
            $rows = [];

            foreach ($cells as $cell) {
                $rows[$cell[Move::ROW]] = $cell;
            }

            return $rows;
        }

        return $cells;
    }

    /**
     * #19 Collect marked cells by a specific symbol ordered by rows. Will be used to calc. the winner.
     */
    public function getMarkedCellsByRows(int $gameId, string $symbol): array
    {
        $q = $this->getMarkedCellsQueryBuilder($gameId, $symbol)
            ->orderBy('a.row', 'ASC')
            ->addOrderBy('a.column', 'ASC')
            ->getQuery();

        return $q->getResult();
    }

    /**
     * #19 Collect marked cells by a specific symbol ordered by columns. Will be used to calc. the winner.
     */
    public function getMarkedCellsByColumns(int $gameId, string $symbol): array
    {
        $q = $this->getMarkedCellsQueryBuilder($gameId, $symbol)
            ->orderBy('a.column', 'ASC')
            ->addOrderBy('a.row', 'ASC')
            ->getQuery();

        return $q->getResult();
    }

    /**
     * #19 Check if there's enough marked cells side-by-side in columns, rows and diagonals with the same symbol.
     */
    public function isWin(Game $game, Move $move): bool
    {
        // #19 Don't continue if there is not enough marked cells.
        // #19 TODO: Don't return the list from DB if there is not enough marked cells.

        // #19 TODO: Most probably the Array needs to be replaced with a Collection.
        $cellCnt = 100; // TODO: Replace this.

        // #19 Don't continue if there is not enough marked cells.
        // #19 TODO: Don't return the list from DB if there is not enough marked cells.
        if ($cellCnt < $game->getMoveCntToWin()) {
            return false;
        }

        if ($this->isRowWin($game, $move, $this->getMarkedCellsInTheRow($game->getId(), $move->getSymbol(), $move->getRow()))) {
            return true;
        }
        if ($this->isColumnWin($game, $move, $this->getMarkedCellsInTheColumn($game->getId(), $move->getSymbol(), $move->getColumn()))) {
            return true;
        }

        return false;
    }

    public function isDiagonalWin(Game $game, Move $move, ?array $cells = []): bool
    {
        $hasWin = false;
        $markedCellCntInRow = 1;

        // #19 Check that the row contains enough selected cells to have a win.
        if (count($cells) < $game->getMoveCntToWin()) {
            return $hasWin;
        }

        if ($game->getMoveCntToWin() === $this->moveRepo->getMarkedCellCntDiagonallyFromLeftToRight($game, $move, $cells)) {
            return true;
        }
        if ($game->getMoveCntToWin() === $this->moveRepo->getMarkedCellCntDiagonallyFromRightToLeft($game, $move, $cells)) {
            return true;
        }

        return false;
    }

    /**
     * #19 Check if there's enough marked cells side-by-side in the row with the same symbol.
     *
     * @param array $cells
     */
    public function isRowWin(Game $game, Move $move, ?array $cells = []): bool
    {
        $hasWin = false;
        $markedCellCntInRow = 1;

        // #19 Check that the row contains enough selected cells to have a win.
        if (count($cells) < $game->getMoveCntToWin()) {
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
    public function isColumnWin(Game $game, Move $move, ?array $cells = []): bool
    {
        $hasWin = false;
        $markedCellCntInColumn = 1;

        // #19 Check that the column contains enough selected cells to have a win.
        if (count($cells) < $game->getMoveCntToWin()) {
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

    public function getMarkedCellCntDiagonallyFromLeftToRight(Game $game, Move $move, ?array $cells = []): int
    {
        $cntInNorthWest = $this->getMarkedCellCntNorthWest($game, $move, $cells);
        $startingCell = 1;
        $cntInSouthEast = $this->getMarkedCellCntSouthEast($game, $move, $cells);

        return $cntInNorthWest + $startingCell + $cntInSouthEast;
    }

    public function getMarkedCellCntDiagonallyFromRightToLeft(Game $game, Move $move, ?array $cells = []): int
    {
        $cntInNorthWest = $this->getMarkedCellCntNorthEast($game, $move, $cells);
        $startingCell = 1;
        $cntInSouthEast = $this->getMarkedCellCntSouthWest($game, $move, $cells);

        return $cntInNorthWest + $startingCell + $cntInSouthEast;
    }

    public function getMarkedCellCntSouthWest(Game $game, Move $move, ?array $cells = []): int
    {
        $markedCellCntInDiagonal = -1;

        // #19 Check that the column contains enough selected cells to have a win.
        if (count($cells) < $game->getMoveCntToWin()) {
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

    public function getMarkedCellCntNorthEast(Game $game, Move $move, ?array $cells = []): int
    {
        $markedCellCntInDiagonal = -1;

        // #19 Check that the column contains enough selected cells to have a win.
        if (count($cells) < $game->getMoveCntToWin()) {
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

    public function getMarkedCellCntSouthEast(Game $game, Move $move, ?array $cells = []): int
    {
        $markedCellCntInDiagonal = -1;

        // #19 Check that the column contains enough selected cells to have a win.
        if (count($cells) < $game->getMoveCntToWin()) {
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

    public function getMarkedCellCntNorthWest(Game $game, Move $move, ?array $cells = []): int
    {
        $markedCellCntInDiagonal = -1;

        // #19 Check that the column contains enough selected cells to have a win.
        if (count($cells) < $game->getMoveCntToWin()) {
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
}
