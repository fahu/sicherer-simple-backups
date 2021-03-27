<?php


namespace App\Services\Backup;


use MicrosoftAzure\Storage\Blob\BlobRestProxy;

class AzureBlobStorageUploader implements CloudStorageUploader
{
    public string $connectionString;
    public string $container;

    /**
     * AzureBlobStorageUploader constructor.
     * @param string $connectionString
     * @param string $container
     */
    public function __construct(string $connectionString, string $container)
    {
        $this->connectionString = $connectionString;
        $this->container = $container;
    }

    function upload(string $path, $content)
    {
        $blobClient = BlobRestProxy::createBlobService($this->connectionString);
        $blobClient->createBlockBlob(container: $this->container, blob: $path, content: $content);
    }
}
