<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Game;
use App\Entity\Move;
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
        parent::__construct($em, Game::class);
    }

    /**
     * #12 Set game board dimensions but don't store it yet.
     * Validations happen in those Entity methods.
     */
    public function selectCell(Game $game, int $row, int $column): Move
    {
        // #17 TODO: Look in DB if such move has already been registered.
        $item = new Move();
        $item->setGameId($game->getId());
        $item->setRow($row);
        $item->setColumn($column);
        $this->save();

        return $item;
    }
}
