<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Interfaces\IGameRepo;

class GameCreatorService
{
    private $gameRepo;

    public function __construct(IGameRepo $gameRepo)
    {
        $this->gameRepo = $gameRepo;
    }

    /**
     * #12 Set game board dimensions.
     *
     * @param array $request
     *
     * @throws \App\Exception\GameValidatorException
     */
    public function setBoardDimensions(?array $request): Game
    {
        if (!isset($request[Game::WIDTH])) {
            throw new \App\Exception\GameValidatorException([Game::WIDTH => Game::ERROR_HEIGHT_WIDTH_INVALID], Game::ERROR_HEIGHT_WIDTH_INVALID_CODE);
        }
        if (!isset($request[Game::HEIGHT])) {
            throw new \App\Exception\GameValidatorException([Game::HEIGHT => Game::ERROR_HEIGHT_WIDTH_INVALID], Game::ERROR_HEIGHT_WIDTH_INVALID_CODE);
        }
        try {
            return $this->gameRepo->setBoardDimensions($request[Game::WIDTH], $request[Game::HEIGHT]);
        } catch (\Error $ex) {
            throw new \App\Exception\GameValidatorException([Game::HEIGHT_WIDTH => Game::ERROR_HEIGHT_WIDTH_INVALID], Game::ERROR_HEIGHT_WIDTH_INVALID_CODE);
        }
    }

    /**
     * #15 Set game rules like how many moves are required to win.
     *
     * @param array $request
     *
     * @throws \App\Exception\GameValidatorException
     */
    public function setRules(Game $game, ?array $request): Game
    {
        if (!isset($request[Game::MOVE_CNT_TO_WIN])) {
            throw new \App\Exception\GameValidatorException([Game::MOVE_CNT_TO_WIN => Game::ERROR_MOVE_CNT_TO_WIN_INVALID], Game::ERROR_MOVE_CNT_TO_WIN_INVALID_CODE);
        }
        try {
            return $this->gameRepo->setRules($game, $request[Game::MOVE_CNT_TO_WIN]);
        } catch (\Error $ex) {
            throw new \App\Exception\GameValidatorException([Game::MOVE_CNT_TO_WIN => Game::ERROR_MOVE_CNT_TO_WIN_INVALID], Game::ERROR_MOVE_CNT_TO_WIN_INVALID_CODE);
        }
    }
}
