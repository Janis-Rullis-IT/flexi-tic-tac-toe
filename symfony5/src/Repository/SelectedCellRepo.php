<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Game;
use App\Entity\Move;
use App\Exception\MoveValidatorException;
use App\Interfaces\ISelectedCellRepo;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * #12 Repo best practices:
 * - https://www.thinktocode.com/2018/03/05/repository-pattern-symfony/.
 * - https://www.thinktocode.com/2019/01/24/doctrine-repositories-should-be-collections-without-flush/.
 */
final class SelectedCellRepo extends BaseRepository implements ISelectedCellRepo
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, Move::class);
    }

    /**
     * #12 Set game board dimensions but don't store it yet.
     * Validations happen in those Entity methods.
     */
    public function select(Game $game, int $row, int $column): Move
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
    public function getAllQueryBuilder(int $gameId, string $symbol): QueryBuilder
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
     * #37 Get the total count of selected cells in this game. Will be used to calculate wins or ties.
     */
    public function getTotalCnt(int $gameId): int
    {
        $q = $this->em->createQueryBuilder('a')
            ->select('COUNT(a.id)')->from(Move::class, 'a')
            ->where('a.gameId = :gameId')->setParameter('gameId', $gameId)->getQuery();

        return (int) $q->getSingleScalarResult();
    }

    /**
     * #19 Collect marked cells by a specific symbol organized in [ROW][COLUMN]. Will be used to calc. the winner.
     *
     * @param type $organizeInRowColumns
     */
    public function getAll(int $gameId, string $symbol, $organizeInRowColumns = true): array
    {
        $q = $this->getAllQueryBuilder($gameId, $symbol)->getQuery();
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
    public function getFromRow(int $gameId, string $symbol, int $rowNumber): array
    {
        $q = $this->getAllQueryBuilder($gameId, $symbol)
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
    public function getFromColumn(int $gameId, string $symbol, int $columnNumber): array
    {
        $q = $this->getAllQueryBuilder($gameId, $symbol)
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
    public function getOrderedByRows(int $gameId, string $symbol): array
    {
        $q = $this->getAllQueryBuilder($gameId, $symbol)
            ->orderBy('a.row', 'ASC')
            ->addOrderBy('a.column', 'ASC')
            ->getQuery();

        return $q->getResult();
    }

    /**
     * #19 Collect marked cells by a specific symbol ordered by columns. Will be used to calc. the winner.
     */
    public function getOrderedByColumns(int $gameId, string $symbol): array
    {
        $q = $this->getAllQueryBuilder($gameId, $symbol)
            ->orderBy('a.column', 'ASC')
            ->addOrderBy('a.row', 'ASC')
            ->getQuery();

        return $q->getResult();
    }
}
