<?php

namespace App\Interfaces;

use App\Entity\Game;

interface IGameService
{
    public function setBoardDimensions(?array $request): Game;

    public function setRules(?array $request): Game;

    public function start(?array $request): Game;
}
