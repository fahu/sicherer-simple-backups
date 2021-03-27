<?php


namespace App\Services\Backup;


interface CloudStorageUploader
{
    function upload(string $path, $content);
}
