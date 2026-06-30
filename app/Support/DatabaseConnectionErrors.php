<?php

namespace App\Support;

use Illuminate\Contracts\Database\LostConnectionDetector;
use Illuminate\Database\LostConnectionException;
use PDOException;
use Throwable;

class DatabaseConnectionErrors
{
    public static function isUnavailable(Throwable $e): bool
    {
        if ($e instanceof LostConnectionException) {
            return true;
        }

        $detector = app(LostConnectionDetector::class);

        for ($current = $e; $current !== null; $current = $current->getPrevious()) {
            if (self::isAccessDenied($current)) {
                return false;
            }

            if ($current instanceof PDOException && in_array((int) $current->getCode(), [2002, 2003], true)) {
                return true;
            }

            if ($detector->causedByLostConnection($current)) {
                return true;
            }
        }

        return false;
    }

    public static function userMessage(): string
    {
        return 'Unable to connect to the database server. Please try again in a few moments or contact your administrator if the problem continues.';
    }

    private static function isAccessDenied(Throwable $e): bool
    {
        return str_contains($e->getMessage(), 'Access denied');
    }
}
