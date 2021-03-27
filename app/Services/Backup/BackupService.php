<?php


namespace App\Services\Backup;


use Exception;
use Ifsnop\Mysqldump;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class BackupService
{
    private const TEMPORARY_BACKUP_BASE_DIRECTORY = "/tmp/cloud-storage-backup";

    private string $temporaryBackupDirectory;

    private false|string $backupDate;

    private string $backupName;

    public function __construct(
        private string $projectName,
        private string $directoryToBackup,
        private DatabaseInfo $databaseInfo,
        private CloudStorageUploader $uploader,
    )
    {
        $hash = bin2Hex(random_bytes(5));

        $this->backupDate = date("Y-m-d");
        $this->backupName = "$this->backupDate-$this->projectName-$hash";
    }

    /**
     * @throws CouldNotCreateTemporaryBackupDirectoryException
     */
    public function createBackupDirectory()
    {
        $temporaryBackupBaseDirectory = BackupService::TEMPORARY_BACKUP_BASE_DIRECTORY;
        $this->temporaryBackupDirectory = "${temporaryBackupBaseDirectory}/$this->backupName";
        mkdir($this->temporaryBackupDirectory, 600, true) ?? throw new CouldNotCreateTemporaryBackupDirectoryException();
    }

    public function backupFiles()
    {
        $this->createZipArchive(
            source: $this->directoryToBackup,
            destination: "$this->temporaryBackupDirectory/files.zip"
        );
    }

    /**
     * @param callable|null $infoHook
     * @throws Exception
     */
    public function dumpDatabase(callable $infoHook = null)
    {
        $dumper = new Mysqldump\Mysqldump(
            dsn: "mysql:host={$this->databaseInfo->host};port={$this->databaseInfo->port};dbname={$this->databaseInfo->schema}",
            user: $this->databaseInfo->user,
            pass: $this->databaseInfo->password
        );

        if ($infoHook) {
            $dumper->setInfoHook($infoHook);
        }

        $dumper->start("$this->temporaryBackupDirectory/database_dump.sql");
    }

    /**
     * @throws CouldNotCreateZipArchiveException
     */
    public function compress()
    {
        $this->createZipArchive(
            source: $this->temporaryBackupDirectory,
            destination: "$this->temporaryBackupDirectory/$this->backupName.zip"
        );
    }

    public function upload()
    {
        $content = fopen("$this->temporaryBackupDirectory/$this->backupName.zip", "r");
        $this->uploader->upload(path: "$this->projectName/$this->backupName.zip", content: $content);
    }

    public function cleanup()
    {
        rmdir($this->temporaryBackupDirectory);
    }

    /**
     * Base script from https://gist.github.com/menzerath/4185113/72db1670454bd707b9d761a9d5e83c54da2052ac
     * @param string $source
     * @param string $destination
     * @return bool
     */
    private function createZipArchive(string $source, string $destination): bool
    {
        if (extension_loaded('zip')) {
            if (file_exists($source)) {
                $zip = new ZipArchive();
                if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
                    $source = realpath($source);
                    if (is_dir($source)) {
                        $iterator = new RecursiveDirectoryIterator($source);
                        $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
                        foreach ($files as $file) {
                            $file = realpath($file);
                            if (is_dir($file)) {
                                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                            } else if (is_file($file)) {
                                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                            }
                        }
                    } else if (is_file($source)) {
                        $zip->addFromString(basename($source), file_get_contents($source));
                    }
                }
                return $zip->close();
            }
        }
        return false;
    }
}

