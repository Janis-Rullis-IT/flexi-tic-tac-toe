<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exception\MoveValidatorException;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

class Move
{
    const MIN_INDEX = 0;
    // #17 Error messages.
    const ERROR_MOVE_INVALID = '#17 Invalid move.';
    const ERROR_MOVE_INVALID_CODE = 200;
    const ERROR_MOVE_ONLY_FOR_ONGOING = '#17 Moves can be set only for an ongoing game.';
    const ERROR_MOVE_ONLY_FOR_ONGOING_CODE = 201;
    const ERROR_ROW_MISSING = '#17 Row is missing.';
    const ERROR_ROW_MISSING_CODE = 202;
    const ERROR_COLUMN_MISSING = '#17 Column is missing.';
    const ERROR_COLUMN_MISSING_CODE = 203;
    // #17 Field names.
    const MOVE = 'move';
    const ROW = 'row';
    const COLUMN = 'column';

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="game_id", type="integer", example=1)
     * @Groups({"PUB", "ID_ERROR"})
     */
    private int $gameId;

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="row", type="integer", example=0)
     * @Groups({"CREATE", "PUB", "ID_ERROR"})
     */
    private int $row;

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="column", type="integer", example=0)
     * @Groups({"CREATE", "PUB", "ID_ERROR"})
     */
    private int $column;

    /**
     * #17 Make sure that the selected row is correct.
     *
     * @param \App\Entity\Game $game
     *
     * @return \self
     *
     * @throws GameValidatorException
     */
    public function setRow(Game $game, int $row): self
    {
        // #17 Make sure that passed values are valid.
        if ($row < self::MIN_INDEX || $row > $this->getMaxAllowedRow($game)) {
            throw new MoveValidatorException([self::MOVE => self::ERROR_MOVE_INVALID], self::ERROR_MOVE_INVALID_CODE);
        }
        // #15 Allow to set dimensions only if it is a new game.
        if (Game::ONGOING !== $game->getStatus()) {
            throw new MoveValidatorException([self::MOVE => self::ERROR_MOVE_ONLY_FOR_ONGOING], self::ERROR_MOVE_ONLY_FOR_ONGOING_CODE);
        }

        $this->row = $row;

        return $this;
    }

    public function getRow(): ?int
    {
        return $this->row;
    }

    /**
     * #17 Make sure that the selected column is correct.
     *
     * @param \App\Entity\Game $game
     *
     * @return \self
     * @thcolumns GameValidatorException
     */
    public function setColumn(Game $game, int $column): self
    {
        // #17 Make sure that passed values are valid.
        if ($column < self::MIN_INDEX || $column > $this->getMaxAllowedColumn($game)) {
            throw new MoveValidatorException([self::MOVE => self::ERROR_MOVE_INVALID], self::ERROR_MOVE_INVALID_CODE);
        }
        // #15 Allow to set dimensions only if it is a new game.
        if (Game::ONGOING !== $game->getStatus()) {
            throw new MoveValidatorException([self::MOVE => self::ERROR_MOVE_ONLY_FOR_ONGOING], self::ERROR_MOVE_ONLY_FOR_ONGOING_CODE);
        }

        $this->column = $column;

        return $this;
    }

    public function getColumn(): ?int
    {
        return $this->column;
    }

    public function setGameId(int $gameId): self
    {
        $this->gameId = $gameId;

        return $this;
    }

    public function getGameId(): ?int
    {
        return $this->gameId;
    }

    public function getMaxAllowedRow($game): ?int
    {
        return $game->getHeight() - 1;
    }

    public function getMaxAllowedColumn($game): ?int
    {
        return $game->getWidth() - 1;
    }
}
