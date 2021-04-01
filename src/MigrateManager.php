<?php

namespace EasySwoole\DatabaseMigrate;

use EasySwoole\Component\Singleton;
use EasySwoole\DatabaseMigrate\Config\Config;
use EasySwoole\Mysqli\Client;

class MigrateManager
{
    use Singleton;

    /** @var Config */
    protected $config;

    /** @var Client */
    protected $client;

    public function __construct(?Config $config = null)
    {
        if (!$config) {
            $config = new Config();
        }
        $this->config = $config;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        if (!$this->client instanceof Client) {
            $this->client = new Client($this->config);
        }
        return $this->client;
    }
}
