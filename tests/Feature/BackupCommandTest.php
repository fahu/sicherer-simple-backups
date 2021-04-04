<?php


namespace Tests\Feature;


use RuntimeException;
use Tests\TestCase;

class BackupCommandTest extends TestCase
{
    public function test_missingConfigFile_throwsException()
    {
        $this->expectException(RuntimeException::class);
        $this->artisan('backup:execute')
            ->execute();
    }
}
