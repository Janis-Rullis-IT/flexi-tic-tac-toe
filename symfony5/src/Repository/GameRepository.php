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
    public function setBoardDimensions(Game $item, int $width, int $height): Game
    {
        $item->setWidth($width);
        $item->setHeight($height);
        $this->save();

        return $item;
    }

    /**
     * #15 Set game rules like how many moves are required to win.
     * Board dimensions are required to be set first.
     */
    public function setRules(Game $item, int $moveCntToWin): Game
    {
        $item = $this->em->getReference(Game::class, $item->getId());
        $item->setMoveCntToWin($moveCntToWin);
        $this->save();

        return $item;
    }

    /**
     * #14 Collect player's current 'ongoing' game or create a new one.
     *
     * @throws GameValidatorException
     */
    public function insertDraftIfNotExist(): Game
    {
        $item = $this->getCurrentDraft();

        // #14 Create if it doesn't exist yet.
        if (empty($item)) {
            $item = new Game();
            $item->setStatus(Game::DRAFT);
            $this->em->persist($item);
            $this->em->flush();
        }

        if (empty($item)) {
            throw new GameValidatorException([Game::STATUS => Game::ERROR_CAN_NOT_CREATE], Game::ERROR_CAN_NOT_CREATE_CODE);
        }

        return $item;
    }

    /*
     * #14 Collect player's current new ('draft') game.
     * @return Game|null
     */
    public function getCurrentDraft(): ?Game
    {
        return $this->findOneBy(['status' => Game::DRAFT]);
    }

    /*
     * #17 Collect player's current new ('ongoing') game.
     * @return Game|null
     */
    public function getCurrentOngoing(): ?Game
    {
        return $this->findOneBy(['status' => Game::ONGOING]);
    }

    /**
     * Collect the current game (draft or ongoing - must be only 1).
     */
    public function getCurrent(): ?Game
    {
        return $this->findOneBy(['status' => [Game::DRAFT, Game::ONGOING]]);
    }

    public function mustFindCurrentOngoing(): ?Game
    {
        $item = $this->getCurrentOngoing();
        if (empty($item)) {
            throw new GameValidatorException([Game::STATUS => Game::ERROR_CAN_NOT_FIND], Game::ERROR_CAN_NOT_FIND_CODE);
        }

        return $item;
    }

    public function mustFindCurrentDraft(): ?Game
    {
        $item = $this->getCurrentDraft();
        if (empty($item)) {
            throw new GameValidatorException([Game::STATUS => Game::ERROR_CAN_NOT_FIND], Game::ERROR_CAN_NOT_FIND_CODE);
        }

        return $item;
    }

    /**
     * #14 Shorthand to write to the database.
     */
    public function save()
    {
        $this->em->flush();
        $this->em->clear();
    }

    /**
     * #17 Mark game as started ('ongoing').
     */
    public function markAsStarted(Game $item): Game
    {
        // #17 A refresh-entity workaround for the field not being updated. https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/unitofwork.html https://www.doctrine-project.org/api/orm/latest/Doctrine/ORM/EntityManager.html
        // TODO: Ask someone about this behaviour.
        $item = $this->em->getReference(Game::class, $item->getId());
        $item->setStatus(Game::ONGOING);
        $this->save($item);

        return $item;
    }

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
