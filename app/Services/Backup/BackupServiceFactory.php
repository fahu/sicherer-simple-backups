<?php


namespace App\Services\Backup;


use InvalidArgumentException;

class BackupServiceFactory
{
    public static function fromConfigFile($path): BackupService
    {
        $config = self::loadConfigOrThrow($path);

        $databaseInfo = new DatabaseInfo(
            host: $config["database"]["host"] ?? throw new InvalidArgumentException(),
            port: $config["database"]["port"] ?? throw new InvalidArgumentException(),
            user: $config["database"]["user"] ?? throw new InvalidArgumentException(),
            password: $config["database"]["password"] ?? throw new InvalidArgumentException(),
            schema: $config["database"]["schema"] ?? throw new InvalidArgumentException(),
        );

        if ($config['storage']['type'] !== 'azure_blob') {
            throw new UnsupportedCloudStorageException();
        }

        $azureBlobStorageUploader = new AzureBlobStorageUploader(
            connectionString: $config["storage"]["connection_string"] ?? throw new InvalidArgumentException(),
            container: $config["storage"]["container"] ?? throw new InvalidArgumentException(),
        );

        return new BackupService(
            projectName: $config["backup"]["name"] ?? throw new InvalidArgumentException(),
            directoryToBackup: $config["backup"]["directory"] ?? throw new InvalidArgumentException(),
            databaseInfo: $databaseInfo,
            uploader: $azureBlobStorageUploader,
        );
    }

    private static function loadConfigOrThrow(string $path): array
    {
        $config = parse_ini_file(filename: $path, process_sections: true, scanner_mode: INI_SCANNER_RAW);
        if (!$config || count($config) < 1) {
            throw new InvalidConfigException();
        }
        return $config;
    }
}
