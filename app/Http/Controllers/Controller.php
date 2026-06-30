<?php

namespace App\Http\Controllers;

use App\Support\DatabaseConnectionErrors;

abstract class Controller
{
    protected function userFacingMessage(\Throwable $e): string
    {
        return DatabaseConnectionErrors::isUnavailable($e)
            ? DatabaseConnectionErrors::userMessage()
            : $e->getMessage();
    }
}
