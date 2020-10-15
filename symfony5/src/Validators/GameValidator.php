<?php

namespace App\Validators;

use App\ErrorsLoader;

class GameValidator
{
    private $errors;
    private $errorsLoader;

    public function __construct(ErrorsLoader $errorsLoader)
    {
        $this->errorsLoader = $errorsLoader;
        $this->errors = [];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
