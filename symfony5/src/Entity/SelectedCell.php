<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exception\SelectedCellValidatorException;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SelectedCellRepo")
 * @ORM\Table(name="`move`")
 */
class SelectedCell
{
    const MIN_INDEX = 0;
    const SYMBOL_X = 'x';
    const SYMBOL_O = 'o';
    const SYMBOL_VALUES = [self::SYMBOL_X, self::SYMBOL_O];
    // #17 Error messages.
    const ERROR_SELECTED_CELL_INVALID = '#17 Invalid SelectedCell.';
    const ERROR_SELECTED_CELL_INVALID_CODE = 200;
    const ERROR_SELECTED_CELL_ONLY_FOR_ONGOING = '#17 SelectedCells can be set only for an ongoing game.';
    const ERROR_SELECTED_CELL_ONLY_FOR_ONGOING_CODE = 201;
    const ERROR_ROW_MISSING = '#17 Row is missing.';
    const ERROR_ROW_MISSING_CODE = 202;
    const ERROR_COLUMN_MISSING = '#17 Column is missing.';
    const ERROR_COLUMN_MISSING_CODE = 203;
    const ERROR_SELECTED_CELL_ALREADY_TAKEN_INVALID = '#18 This cell is already taken.';
    const ERROR_SELECTED_CELL_ALREADY_TAKEN_INVALID_CODE = 204;
    // #17 Field names.
    const SELECTED_CELL = 'selected_cell';
    const ROW = 'row';
    const COLUMN = 'column';
    const SYMBOL = 'symbol';
    const IS_LAST = 'is_last';
    const IS_TIE = 'is_tie';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @SWG\Property(property="id", type="integer", example=1)
     * @Groups({"ID_ERROR"})
     */
    private int $id;

    /**
     * @ORM\Column(name="`game_id`", type="integer")
     * @SWG\Property(property="game_id", type="integer", example=1)
     * @Groups({"ID_ERROR"})
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

    /**
     * @ORM\Column(name="`symbol`", type="string")
     * @SWG\Property(property="symbol", type="string", example="x")
     * @Groups({"PUB", "ID_ERROR"})
     */
    private string $symbol = self::SYMBOL_X;

    /**
     * @SWG\Property(property="is_last", type="boolean", example=false)
     * @Groups({"PUB", "ID_ERROR"})
     */
    private bool $is_last = false;

    /**
     * @SWG\Property(property="is_tie", type="boolean", example=false)
     * @Groups({"PUB", "ID_ERROR"})
     */
    private bool $is_tie = false;

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
     * @throws SelectedCellValidatorException
     */
    public function setRow(Game $game, int $row): self
    {
        // #17 Make sure that the SelectedCell is not outside the board.
        if ($row < self::MIN_INDEX || $row > $this->getMaxAllowedRow($game)) {
            throw new SelectedCellValidatorException([self::SELECTED_CELL => self::ERROR_SELECTED_CELL_INVALID], self::ERROR_SELECTED_CELL_INVALID_CODE);
        }
        // #15 Allow to set SelectedCell only if it is a started game.
        if (Game::ONGOING !== $game->getStatus()) {
            throw new SelectedCellValidatorException([self::SELECTED_CELL => self::ERROR_SELECTED_CELL_ONLY_FOR_ONGOING], self::ERROR_SELECTED_CELL_ONLY_FOR_ONGOING_CODE);
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
     *
     * @throws SelectedCellValidatorException
     */
    public function setColumn(Game $game, int $column): self
    {
        // #17 Make sure that the SelectedCell is not outside the board.
        if ($column < self::MIN_INDEX || $column > $this->getMaxAllowedColumn($game)) {
            throw new SelectedCellValidatorException([self::SELECTED_CELL => self::ERROR_SELECTED_CELL_INVALID], self::ERROR_SELECTED_CELL_INVALID_CODE);
        }
        // #15 Allow to set SelectedCell only if it is a started game.
        if (Game::ONGOING !== $game->getStatus()) {
            throw new SelectedCellValidatorException([self::SELECTED_CELL => self::ERROR_SELECTED_CELL_ONLY_FOR_ONGOING], self::ERROR_SELECTED_CELL_ONLY_FOR_ONGOING_CODE);
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

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setIsLast(bool $isLast): self
    {
        $this->is_last = $isLast;

        return $this;
    }

    public function getIsLast(): ?bool
    {
        return $this->is_last;
    }

    public function setIsTie(bool $isTie): self
    {
        $this->is_tie = $isTie;

        return $this;
    }

    public function getIsTie(): ?bool
    {
        return $this->is_tie;
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
            self::SYMBOL => $this->getSymbol(), self::IS_LAST => $this->getIsLast(),
            self::IS_TIE => $this->getIsTie(),
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
