<?php


namespace App\Services\Backup;


use Exception;
use Throwable;

class CouldNotCreateZipArchiveException extends Exception
{
    public function __construct($message = "Cannot create ZIP archive of backup", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
