<?php


namespace App\Services\Backup;


use Exception;
use Throwable;

class CouldNotCreateTemporaryBackupDirectoryException extends Exception
{
    public function __construct($message = "Could not create temporary backup directory.", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
