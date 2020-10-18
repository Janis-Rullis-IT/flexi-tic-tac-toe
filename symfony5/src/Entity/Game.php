<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exception\GameValidatorException;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

class Game
{
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
    // #12 Field names.
    const WIDTH = 'width';
    const HEIGHT = 'height';
    const HEIGHT_WIDTH = 'height_width';
    const MOVE_CNT_TO_WIN = 'move_cnt_to_win';

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="width", type="integer", example=3)
     * @Groups({"CREATE", "PUB", "ID_ERROR"})
     */
    private int $width;

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="height", type="integer", example=3)
     * @Groups({"CREATE", "PUB", "ID_ERROR"})
     */
    private int $height;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @SWG\Property(property="move_cnt_to_win", type="integer", example=3)
     * @Groups({"CREATE", "PUB", "ID_ERROR"})
     */
    private ?int $moveCntToWin = null;

    /**
     * #12 Make sure that the width is correct.
     *
     * @return \self
     *
     * @throws GameValidatorException
     */
    public function setWidth(int $width): self
    {
        if ($width < self::MIN_HEIGHT_WIDTH || $width > self::MAX_HEIGHT_WIDTH) {
            throw new GameValidatorException([self::WIDTH => self::ERROR_HEIGHT_WIDTH_INVALID], self::ERROR_HEIGHT_WIDTH_INVALID_CODE);
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
        if ($height < self::MIN_HEIGHT_WIDTH || $height > self::MAX_HEIGHT_WIDTH) {
            throw new GameValidatorException([self::HEIGHT => self::ERROR_HEIGHT_WIDTH_INVALID], self::ERROR_HEIGHT_WIDTH_INVALID_CODE);
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
     */
    public function setMoveCntToWin(int $moveCntToWin): self
    {
        // #15 Move count to win must be no smaller than the min board dimensions or go outside the board.
        if ($moveCntToWin < $this->getMinDimension() || $moveCntToWin > $this->getMaxDimension()) {
            throw new GameValidatorException([self::MOVE_CNT_TO_WIN => self::ERROR_MOVE_CNT_TO_WIN_INVALID], self::ERROR_MOVE_CNT_TO_WIN_INVALID_CODE);
        }

        $this->moveCntToWin = $moveCntToWin;

        return $this;
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

    public function getMoveCntToWin(): ?int
    {
        return $this->moveCntToWin;
    }
}
