<?php


namespace App\Services\Backup;


class DatabaseInfo
{
    public string $host;
    public string $port;
    public string $user;
    public string $password;
    public string $schema;

    /**
     * DatabaseInfo constructor.
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $password
     * @param string $schema
     */
    public function __construct(string $host, string $port, string $user, string $password, string $schema)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->schema = $schema;
    }
}
