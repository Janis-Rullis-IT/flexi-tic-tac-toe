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
    // #12 Field names.
    const WIDTH = 'width';
    const HEIGHT = 'height';
    const HEIGHT_WIDTH = 'height_width';

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

    public function setWidth(int $width): self
    {
        // #12 Make sure that the width is correct.
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

    public function setHeight(int $height): self
    {
        // #12 Make sure that the range is correct.
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
}
