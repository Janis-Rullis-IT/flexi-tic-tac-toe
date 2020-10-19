<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Move;
use App\Exception\MoveValidatorException;
use App\Interfaces\IGameRepo;
use App\Interfaces\IMoveRepo;

class MoveService
{
    private $gameRepo;
    private $moveRepo;

    public function __construct(IGameRepo $gameRepo, IMoveRepo $moveRepo)
    {
        $this->gameRepo = $gameRepo;
        $this->moveRepo = $moveRepo;
    }

    /**
     * #17 Select the cell.
     *
     * @param array $request
     *
     * @throws \App\Exception\MoveValidatorException
     */
    public function selectCell(?array $request): Move
    {
        if (!isset($request[Move::ROW])) {
            throw new MoveValidatorException([Move::ROW => Move::ERROR_ROW_MISSING], Move::ERROR_ROW_MISSING_CODE);
        }
        if (!isset($request[Move::COLUMN])) {
            throw new MoveValidatorException([Move::COLUMN => Move::ERROR_COLUMN_MISSING], Move::ERROR_COLUMN_MISSING_CODE);
        }
        try {
            $game = $this->gameRepo->mustFindCurrentDraft();

            return $this->moveRepo->selectCell($game, $request[Move::ROW], $request[Move::COLUMN]);
        } catch (\Error $ex) {
            throw new MoveValidatorException([Move::MOVE => Move::ERROR_MOVE_INVALID], Move::ERROR_MOVE_INVALID_CODE);
        }
    }
}
