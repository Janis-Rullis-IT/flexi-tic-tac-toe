<?php

namespace App\Interfaces;

use App\Entity\Game;

interface IGameRepo
{
    public function setBoardDimensions(Game $item, int $width, int $height): Game;

    public function setRules(Game $item, int $selectedCellCntToWin): Game;

    public function insertDraftIfNotExist(): Game;

    public function getCurrent(): ?Game;

    public function getCurrentDraft(): ?Game;

    public function mustFindCurrentDraft(): ?Game;

    public function save();

    public function markAsStarted(Game $item): Game;

    public function markAsCompleted(Game $item): Game;

    public function toggleNextSymbol(Game $item): Game;
}
