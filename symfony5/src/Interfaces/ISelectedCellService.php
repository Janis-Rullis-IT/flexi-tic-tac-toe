<?php

namespace App\Interfaces;

use App\Entity\SelectedCell;

interface ISelectedCellService
{
    public function select(?array $request): SelectedCell;
}
