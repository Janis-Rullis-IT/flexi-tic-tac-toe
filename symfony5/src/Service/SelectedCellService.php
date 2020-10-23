<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\SelectedCell;
use App\Exception\SelectedCellValidatorException;
use App\Interfaces\IGameRepo;
use App\Interfaces\ISelectedCellRepo;
use App\Interfaces\IVictoryCalculationService;

final class SelectedCellService
{
    private $gameRepo;
    private $selectedCellRepo;
    private $victoryCalculationService;

    public function __construct(IGameRepo $gameRepo, ISelectedCellRepo $selectedCellRepo, IVictoryCalculationService $victoryCalculationService)
    {
        $this->gameRepo = $gameRepo;
        $this->selectedCellRepo = $selectedCellRepo;
        $this->victoryCalculationService = $victoryCalculationService;
    }

    /**
     * #17 Select the cell.
     *
     * @param array $request
     *
     * @throws \App\Exception\SelectedCellValidatorException
     */
    public function select(?array $request): SelectedCell
    {
        if (!isset($request[SelectedCell::ROW])) {
            throw new SelectedCellValidatorException([SelectedCell::ROW => SelectedCell::ERROR_ROW_MISSING], SelectedCell::ERROR_ROW_MISSING_CODE);
        }
        if (!isset($request[SelectedCell::COLUMN])) {
            throw new SelectedCellValidatorException([SelectedCell::COLUMN => SelectedCell::ERROR_COLUMN_MISSING], SelectedCell::ERROR_COLUMN_MISSING_CODE);
        }
        try {
            $game = $this->gameRepo->mustFindCurrentOngoing();
            $selectedCell = $this->selectedCellRepo->select($game, $request[SelectedCell::ROW], $request[SelectedCell::COLUMN]);
            $game = $this->gameRepo->toggleNextSymbol($game);
            $totalSelectedCellCnt = $this->selectedCellRepo->getTotalCnt($game->getId());

            if ($this->victoryCalculationService->isWin($totalSelectedCellCnt, $game, $selectedCell)) {
                $this->gameRepo->markAsCompleted($game);
                $selectedCell->setIsLast(true);
            } elseif ($this->victoryCalculationService->isTie($game, $totalSelectedCellCnt)) {
                $this->gameRepo->markAsCompleted($game);
                $selectedCell->setIsLast(true);
                $selectedCell->setIsTie(true);
            }

            return $selectedCell;
        } catch (\Error $ex) {
            throw new SelectedCellValidatorException([SelectedCell::SELECTED_CELL => SelectedCell::ERROR_SELECTED_CELL_INVALID], SelectedCell::ERROR_SELECTED_CELL_INVALID_CODE);
        }
    }
}
