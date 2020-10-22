<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exception\GameValidatorException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameRepository")
 * @ORM\Table(name="`game`")
 */
class Game
{
    const DRAFT = 'draft';
    const ONGOING = 'ongoing';
    const COMPLETED = 'completed';
    const STATUS_VALUES = [self::DRAFT, self::ONGOING, self::COMPLETED];
    // #12 Limits.
    // #12 TODO: Maybe replace this hard-code with reading from DB? In case if def can be changed.
    const MIN_HEIGHT_WIDTH = 2;
    const MAX_HEIGHT_WIDTH = 20;
    // #12 Error messages.
    const ERROR_HEIGHT_WIDTH_INVALID = '#12 Width and height must be an integer from 2 to 20.';
    const ERROR_HEIGHT_WIDTH_INVALID_CODE = 100;
    const ERROR_WIDTH_ALREADY_SET = '#12 Can not change the width for a game that has already started.';
    const ERROR_WIDTH_ALREADY_SET_CODE = 101;
    const ERROR_MOVE_CNT_TO_WIN_INVALID = '#15 Move count to win must be an integer not smaller than 2 and not bigger than the height or width.';
    const ERROR_MOVE_CNT_TO_WIN_INVALID_CODE = 102;
    const ERROR_STATUS_INVALID = '#14 Status must be \'draft\', \'ongoing\' or \'completed\'.';
    const ERROR_STATUS_INVALID_CODE = 103;
    const ERROR_ONLY_FOR_DRAFT = '#14 Allowed only for a game with a \'draft\' status.';
    const ERROR_ONLY_FOR_DRAFT_CODE = 104;
    const ERROR_CAN_NOT_CREATE = '#14 Can not create a new game.';
    const ERROR_CAN_NOT_CREATE_CODE = 105;
    const ERROR_CAN_NOT_FIND = '#14 Can not find the game.';
    const ERROR_CAN_NOT_FIND_CODE = 106;
    const ERROR_STATUS_DRAFT_INVALID = '#17 DRAFT should be only set once.';
    const ERROR_STATUS_DRAFT_INVALID_CODE = 107;
    const ERROR_STATUS_ONGOING_INVALID = '#17 ONGOING requires to have a draft status and board dimensions and rules set.';
    const ERROR_STATUS_ONGOING_INVALID_CODE = 108;
    // #12 Field names.
    const ID = 'id';
    const STATUS = 'status';
    const WIDTH = 'width';
    const HEIGHT = 'height';
    const HEIGHT_WIDTH = 'height_width';
    const MOVE_CNT_TO_WIN = 'move_cnt_to_win';
    const MOVES = 'moves';
    const NEXT_SYMBOL = 'next_symbol';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @SWG\Property(property="id", type="integer", example=1)
     * @Groups({"PUB"})
     */
    private int $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @SWG\Property(property="status", type="string", example="ongoing")
     * @Groups({"PUB", "ID_ERROR"})
     */
    private ?string $status = null;

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="width", type="integer", example=3)
     * @Groups({"CREATE", "BOARD", "PUB", "ID_ERROR"})
     */
    private int $width = 3;

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="height", type="integer", example=3)
     * @Groups({"CREATE", "BOARD", "PUB", "ID_ERROR"})
     */
    private int $height = 3;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @SWG\Property(property="move_cnt_to_win", type="integer", example=3)
     * @Groups({"CREATE", "PUB", "RULES"})
     */
    private ?int $move_cnt_to_win = null;

    /**
     * @ORM\Column(name="`next_symbol`", type="string")
     * @SWG\Property(property="next_symbol", type="string", example="x")
     * @Groups({"CREATE", "PUB", "ID_ERROR"})
     */
    private string $next_symbol = Move::SYMBOL_X;

    /**
     * @ORM\ManyToMany(targetEntity="Move")
     * @ORM\JoinTable(name="`move`",
     *      joinColumns={@ORM\JoinColumn(name="game_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="id", referencedColumnName="id", unique=true)}
     * )
     * @SWG\Property(property="moves", type="array", @SWG\Items(@Model(type=Move::class)))
     */
    private ?Collection $moves = null;

    public function __construct()
    {
        $this->moves = new ArrayCollection();
    }

    /**
     * #30 Collect game's moves
     * Collected using annotation JOIN. See `$moves`.
     */
    public function getMoves()
    {
        return $this->moves;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * #14 #17 Set games status like 'ongoing'.
     *
     * @return \self
     *
     * @throws GameValidatorException
     */
    public function setStatus(string $status): self
    {
        // #14 Make sure that passed values are valid.
        if (!in_array($status, self::STATUS_VALUES)) {
            throw new GameValidatorException([self::STATUS => self::ERROR_STATUS_INVALID], self::ERROR_STATUS_INVALID_CODE);
        }

        switch ($status) {
            // #17 DRAFT should be only set once.
            case self::DRAFT:
                if (!(self::DRAFT === $this->getStatus() || null === $this->getStatus())) {
                    throw new GameValidatorException([self::STATUS => self::ERROR_STATUS_DRAFT_INVALID], self::ERROR_STATUS_DRAFT_INVALID_CODE);
                }
                break;
            // #17 ONGOING requires to have a draft status and board dimensions and rules set.
            case self::ONGOING:
                if (!(self::DRAFT === $this->getStatus() && !empty($this->getHeight()) && !empty($this->getWidth()) && !empty($this->getMoveCntToWin()))) {
                    throw new GameValidatorException([self::STATUS => self::ERROR_STATUS_ONGOING_INVALID], self::ERROR_STATUS_ONGOING_INVALID_CODE);
                }
        }

        $this->status = $status;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * #12 Make sure that the width is correct.
     *
     * @return \self
     *
     * @throws GameValidatorException
     */
    public function setWidth(int $width): self
    {
        // #14 Make sure that passed values are valid.
        if ($width < self::MIN_HEIGHT_WIDTH || $width > self::MAX_HEIGHT_WIDTH) {
            throw new GameValidatorException([self::WIDTH => self::ERROR_HEIGHT_WIDTH_INVALID], self::ERROR_HEIGHT_WIDTH_INVALID_CODE);
        }
        // #15 Allow to set dimensions only if it is a new game.
        if (self::DRAFT !== $this->getStatus()) {
            throw new GameValidatorException([self::WIDTH => self::ERROR_ONLY_FOR_DRAFT], self::ERROR_ONLY_FOR_DRAFT_CODE);
        }

        $this->width = $width;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * #12 Make sure that the range is correct.
     *
     * @return \self
     *
     * @throws GameValidatorException
     */
    public function setHeight(int $height): self
    {
        // #14 Make sure that passed values are valid.
        if ($height < self::MIN_HEIGHT_WIDTH || $height > self::MAX_HEIGHT_WIDTH) {
            throw new GameValidatorException([self::HEIGHT => self::ERROR_HEIGHT_WIDTH_INVALID], self::ERROR_HEIGHT_WIDTH_INVALID_CODE);
        }
        // #15 Allow to set dimensions only if it is a new game.
        if (self::DRAFT !== $this->getStatus()) {
            throw new GameValidatorException([self::HEIGHT => self::ERROR_ONLY_FOR_DRAFT], self::ERROR_ONLY_FOR_DRAFT_CODE);
        }

        $this->height = $height;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * #15 Set how many moves are required to win.
     * Board dimensions are required to be set first.
     *
     * @return \self
     *
     * @throws GameValidatorException
     */
    public function setMoveCntToWin(int $moveCntToWin): self
    {
        // #15 Move count to win must be no smaller than the min board dimensions or go outside the board.
        if ($moveCntToWin < $this->getMinDimension() || $moveCntToWin > $this->getMaxDimension()) {
            throw new GameValidatorException([self::MOVE_CNT_TO_WIN => self::ERROR_MOVE_CNT_TO_WIN_INVALID], self::ERROR_MOVE_CNT_TO_WIN_INVALID_CODE);
        }
        // #15 Allow to set dimensions only if it is a new game.
        if (self::DRAFT !== $this->getStatus()) {
            throw new GameValidatorException([self::MOVE_CNT_TO_WIN => self::ERROR_ONLY_FOR_DRAFT], self::ERROR_ONLY_FOR_DRAFT_CODE);
        }

        $this->move_cnt_to_win = $moveCntToWin;

        return $this;
    }

    public function getMoveCntToWin(): ?int
    {
        return $this->move_cnt_to_win;
    }

    public function setNextSymbol(): self
    {
        // #33 A move was made, need to change the next symbol.
        $this->next_symbol = Move::SYMBOL_X === $this->getNextSymbol() ? Move::SYMBOL_O : Move::SYMBOL_X;

        return $this;
    }

    public function getNextSymbol(): ?string
    {
        return $this->next_symbol;
    }

    /**
     * #15 Get the smallest dimension - width or height.
     */
    public function getMinDimension(): int
    {
        return $this->getHeight() >= $this->getWidth() ? $this->getWidth() : $this->getHeight();
    }

    /**
     * #15 Get the biggest dimension - width or height.
     */
    public function getMaxDimension(): int
    {
        return $this->getHeight() >= $this->getWidth() ? $this->getHeight() : $this->getWidth();
    }

    /**
     * #37 Get the total cell count from the height and width. Required to calc. a tie.
     */
    public function getTotalCellCnt(): int
    {
        return $this->getHeight() * $this->getWidth();
    }

    /**
     * #15 Convert the Entity to array in unified manner.
     * Will give same result in different endpoints.
     *
     * @param array $fields
     */
    public function toArray(?array $fields = [], $relations = []): array
    {
        $return = [];
        // #15 Contains most popular fields. Add a field is necessary.
        $return = $this->toArrayFill($fields);

        // #30 Fill relations.
        if (!empty($relations)) {
            foreach ($relations as $relation) {
                switch ($relation) {
                    case Game::MOVES:
                        $moves = $this->getMoves();
                        $return[Game::MOVES] = [];
                        if (!empty($moves)) {
                            foreach ($moves as $move) {
                                // #32 This format is better because when drawing the board it's faster to make sure that it's selected.
                                // https://github.com/janis-rullis/lm1-symfony5-vue2-api/issues/32#issuecomment-712735160
                                $return[Game::MOVES][$move->getRow()][$move->getColumn()] = $move->toArray();
                            }
                        }
                        break;
                    default: null;
                }
            }
        }

        return $return;
    }

    /**
     * #15 Fill order's fields.
     */
    private function toArrayFill(?array $fields = []): array
    {
        $return = [];
        $allFields = [
            self::ID => $this->getId(), self::STATUS => $this->getStatus(),
            self::WIDTH => $this->getWidth(), self::HEIGHT => $this->getHeight(),
            self::MOVE_CNT_TO_WIN => $this->getMoveCntToWin(), self::NEXT_SYMBOL => $this->getNextSymbol(),
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
