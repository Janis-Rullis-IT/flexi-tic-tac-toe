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
	const ERROR_WIDTH_MUST_BE_INT = '#12 Width must be an integer from 2 to 20.';
	const ERROR_WIDTH_MUST_BE_INT_CODE = 100;
	const ERROR_WIDTH_ALREADY_SET = '#12 Can not change the width for a game that has already started.';
	const ERROR_WIDTH_ALREADY_SET_CODE = 101;
}
