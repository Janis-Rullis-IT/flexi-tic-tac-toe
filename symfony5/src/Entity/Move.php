<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exception\MoveValidatorException;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MoveRepository")
 * @ORM\Table(name="`move`")
 */
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
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @SWG\Property(property="id", type="integer", example=1)
     * @Groups({"PUB"})
     */
    private int $id;

    /**
     * @ORM\Column(name="`game_id`", type="integer")
     * @SWG\Property(property="game_id", type="integer", example=1)
     * @Groups({"PUB", "ID_ERROR"})
     */
    private int $gameId;

    /**
     * @ORM\Column(name="`row`", type="integer")
     * @SWG\Property(property="row", type="integer", example=0)
     * @Groups({"CREATE", "PUB", "ID_ERROR"})
     */
    private int $row;

    /**
     * @ORM\Column(name="`column`", type="integer")
     * @SWG\Property(property="column", type="integer", example=0)
     * @Groups({"CREATE", "PUB", "ID_ERROR"})
     */
    private int $column;

    public function getId(): ?int
    {
        return $this->id;
    }

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
        // #17 Make sure that the move is not outside the board.
        if ($row < self::MIN_INDEX || $row > $this->getMaxAllowedRow($game)) {
            throw new MoveValidatorException([self::MOVE => self::ERROR_MOVE_INVALID], self::ERROR_MOVE_INVALID_CODE);
        }
        // #15 Allow to set move only if it is a started game.
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
        // #17 Make sure that the move is not outside the board.
        if ($column < self::MIN_INDEX || $column > $this->getMaxAllowedColumn($game)) {
            throw new MoveValidatorException([self::MOVE => self::ERROR_MOVE_INVALID], self::ERROR_MOVE_INVALID_CODE);
        }
        // #15 Allow to set move only if it is a started game.
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

    public function getMaxAllowedRow(Game $game): ?int
    {
        return $game->getHeight() - 1;
    }

    public function getMaxAllowedColumn(Game $game): ?int
    {
        return $game->getWidth() - 1;
    }

    public function toArray(?array $fields = [], $relations = []): array
    {
        $return = [];
        // #17 Contains most popular fields. Add a field is necessary.
        $return = $this->toArrayFill($fields);

        return $return;
    }

    /**
     * #17 Fill order's fields.
     */
    private function toArrayFill(?array $fields = []): array
    {
        $return = [];
        $allFields = [
            self::ROW => $this->getRow(), self::COLUMN => $this->getColumn(),
        ];

        if (empty($fields)) {
            return $allFields;
        }

        foreach ($fields as $field) {
            $return[$field] = isset($allFields[$field]) ? $allFields[$field] : null;
        }

        return $return;
    }
}
