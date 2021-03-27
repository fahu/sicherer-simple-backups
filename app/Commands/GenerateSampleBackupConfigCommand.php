<?php

namespace App\Commands;

use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class GenerateSampleBackupConfigCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'backup:config:generate-sample';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generates a sample config file (config.sample.ini)';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $content = <<<SAMPLE_FILE_CONTENT
        ; This is a sample config file. Please use double quotes (") to avoid information
        ; being truncated (e.g. in the `connection_string`).

        [database]
        host = "127.0.0.1"
        port = "3306"
        user = "root"
        password = "password"
        schema = "db_schema_name"

        [storage]
        type = "azure_blob"
        connection_string = "CONNECTION_STRING_WITH_SAS_TOKEN"
        container = "backups"

        [backup]
        name = "project_name"
        directory = "/path/to/directory"
        SAMPLE_FILE_CONTENT;

        File::put(getcwd() . "/config-test.sample.ini", $content);
    }
}
