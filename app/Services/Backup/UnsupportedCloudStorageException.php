<?php


namespace App\Services\Backup;


use RuntimeException;
use Throwable;

class UnsupportedCloudStorageException extends RuntimeException
{
    public function __construct($message = "The specified cloud storage is not supported.", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
