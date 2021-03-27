<?php


namespace App\Services\Backup;


use RuntimeException;
use Throwable;

class InvalidConfigException extends RuntimeException
{
    public function __construct($message = "The specified config is not valid.", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
