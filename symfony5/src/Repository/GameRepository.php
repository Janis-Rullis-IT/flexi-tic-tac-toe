<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Game;
use App\Exception\GameValidatorException;
use App\Interfaces\IGameRepo;
use Doctrine\ORM\EntityManagerInterface;

/**
 * #12 Repo best practices:
 * - https://www.thinktocode.com/2018/03/05/repository-pattern-symfony/.
 * - https://www.thinktocode.com/2019/01/24/doctrine-repositories-should-be-collections-without-flush/.
 */
final class GameRepository extends BaseRepository implements IGameRepo
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, Game::class);
    }

    /**
     * #12 Set game board dimensions but don't store it yet.
     * Validations happen in those Entity methods.
     */
    public function setBoardDimensions(int $width, int $height): Game
    {
        $item = new Game();
        $item->setWidth($width);
        $item->setHeight($height);

        return $item;
    }

    /*
     * #15 Set game rules like how many moves are required to win.
     * Board dimensions are required to be set first.
     *
     */
    public function setRules(Game $item, int $moveCntToWin): Game
    {
        // #15 Collect game first.
        $item->setMoveCntToWin($moveCntToWin);

        return $item;
    }

    /*
     * #12 Collect player's current 'ongoing' game or create a new one.
     *
     * @param int $playerId
     * @return Game
     * @throws GameValidatorException
     */
//	public function insertDraftIfNotExist(int $playerId): Game
//	{
//		$item = $this->getCurrentDraft($playerId);
//
//		// #12 Create if it doesn't exist yet.
//		if (empty($item)) {
//			$item = new Game();
//			$item->setPlayerId($playerId);
//			$item->setStatus(Game::ONGOING);
//			$this->em->persist($item);
//			$this->em->flush();
//		}
//
//		if (empty($item)) {
//			throw new GameValidatorException([Game::ORDER_ID => Game::CANT_CREATE]);
//		}
//
//		return $item;
//	}

    /*
     * #12 Collect player's current 'ongoing' game.
     *
     * @param int $playerId
     * @return Game|null
     */
//	public function getCurrentDraft(int $playerId): ?Game
//	{
//		return $this->findOneBy(['player_id' => $playerId, 'status' => Game::ONGOING]);
//	}

    /*
     * #12 Shorthand to write to the database.
     */
//	public function save()
//	{
//		$this->em->flush();
//		$this->em->clear();
//	}

    /*
     * #12 Mark the game as completed.
     *
     * @param Game $game
     * @return Game
     */
//	public function markAsCompleted(Game $game): Game
//	{
//		// #12 A refresh-entity workaround for the field not being updated. https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/unitofwork.html https://www.doctrine-project.org/api/orm/latest/Doctrine/ORM/EntityManager.html
//		// TODO: Ask someone about this behaviour.
//		$game = $this->em->getReference(Game::class, $game->getId());
//		$game->setStatus(Game::COMPLETED);
//		$this->save($game);
//
//		return $game;
//	}
    /*
     * #12 Find game by id. Throw an exception if not found.
     *
     * @param int $userId
     * @param int $gameId
     * @return Game
     * @throws GameValidatorException
     */
//	public function mustFindUsersGame(int $userId, int $gameId): Game
//	{
//		$game = $this->findOneBy(['player_id' => $userId, 'id' => $gameId]);
//		if (empty($game)) {
//			throw new GameValidatorException([Game::ID => Game::INVALID], 1);
//		}
//
//		return $game;
//	}

    /*
     * #12 Find user's games.
     *
     * @param int $userId
     * @return array
     */
//	public function mustFindUsersGames(int $userId): array
//	{
//		return $this->findBy(['player_id' => $userId]);
//	}
}
