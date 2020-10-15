<?php
namespace App\Entity;

use App\Exception\GameValidatorException;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

class Game
{

	// #12 Limits.
	// #12 TODO: Maybe replace this hard-code with reading from DB? In case if def can be changed.
	const MIN_WIDTH = 2;
	const MAX_WIDTH = 20;
	// #12 Error messages.
	const ERROR_WIDTH_INVALID = '#12 Width must be an integer from 2 to 20.';
	const ERROR_WIDTH_INVALID_CODE = 100;
	const ERROR_WIDTH_ALREADY_SET = '#12 Can not change the width for a game that has already started.';
	const ERROR_WIDTH_ALREADY_SET_CODE = 101;
	// #12 Field names.
	const WIDTH = 'width';

	/**
	 * @ORM\Column(type="integer")
	 * @SWG\Property(property="width", type="integer", example=3)
	 * @Groups({"PUB"})
	 */
	private int $width;

	public function setWidth(int $width): self
	{
		// #12 Make sure that the width range is correct.
		if ($width < self::MIN_WIDTH || $width > self::MAX_WIDTH) {
			throw new GameValidatorException([self::WIDTH => self::ERROR_WIDTH_INVALID], self::ERROR_WIDTH_INVALID_CODE);
		}

		$this->width = $width;

		return $this;
	}

	public function getWidth(): ?int
	{
		return $this->width;
	}
}
