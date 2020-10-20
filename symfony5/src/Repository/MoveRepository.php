<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Game;
use App\Entity\Move;
use App\Exception\MoveValidatorException;
use App\Interfaces\IMoveRepo;
use Doctrine\ORM\EntityManagerInterface;

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
    public function getMarkedCells(int $gameId, string $symbol): array
    {
        $q = $this->em->createQueryBuilder('a')
            ->select('a.row', 'a.column')
            ->from(Move::class, 'a')
            ->where('a.gameId = :gameId')
            ->andWhere('a.symbol = :symbol')
            ->orderBy('a.row', 'ASC')
            ->addOrderBy('a.column', 'ASC')
            ->setParameter('gameId', $gameId)
            ->setParameter('symbol', $symbol)
            ->getQuery();

        return $q->getResult();
    }

    /**
     * #53 Get a set amount of users that has any product.
     * Necessary for testing purposes.
     */
    public function getUsersWithProducts(int $count = 3): array
    {
        $q = $this->getUsersWithProductsQuery($count)->getQuery();

        return $q->getResult();
    }
}
