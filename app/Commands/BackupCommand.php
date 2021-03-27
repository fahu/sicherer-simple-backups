<?php


namespace App\Commands;


use App\Services\Backup\BackupServiceFactory;
use Exception;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Input\InputArgument;

class BackupCommand extends Command
{
    const ARGUMENT_NAME_CONFIG = "config";
    const OPTION_NAME_CLEANUP = "cleanup";

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = "backup:execute {--c|cleanup}";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = "Creates a file and database backup for the specified project";

    protected function configure()
    {
        $this->addArgument(
            name: BackupCommand::ARGUMENT_NAME_CONFIG,
            mode: InputArgument::REQUIRED,
            description: "The path to the config file."
        );

        $this->addOption(
            name: BackupCommand::OPTION_NAME_CLEANUP,
            shortcut: "c",
            description: "Cleanup temporary created directories during backup."
        );

        parent::configure();
    }

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle()
    {
        $configPath = $this->argument(BackupCommand::ARGUMENT_NAME_CONFIG);
        $backupService = BackupServiceFactory::fromConfigFile($configPath);

        $this->task("Preparing temporary backup location", function () use ($backupService) {
            $backupService->createBackupDirectory();
        });

        $this->task("Backup files", function () use ($backupService) {
            $backupService->backupFiles();
        });

        $this->task("Dumping database", function () use ($backupService) {
            $progressInfo = function ($object, $info) {
                if ($object === 'table') {
                    $this->output->progressAdvance();
                    echo $info['name'] . " (" . $info['rowCount'] . ")";
                }
            };

            $this->output->progressStart();
            $backupService->dumpDatabase($progressInfo);
            $this->output->progressFinish();
            $this->newLine();

            return true;
        });

        $this->task("Compressing backup", function () use ($backupService) {
            $backupService->compress();
        });

        $this->task("Uploading to Azure", function () use ($backupService) {
            $backupService->upload();
        });

        if ($this->option(BackupCommand::OPTION_NAME_CLEANUP)) {
            $this->task("Cleaning up", function () use ($backupService) {
                $backupService->cleanup();
            });
        }

        $this->info('Backup finished successfully');
    }
}
