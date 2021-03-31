<?php

namespace EasySwoole\DatabaseMigrate\Databases;

use EasySwoole\Component\Singleton;
use EasySwoole\DatabaseMigrate\Databases\Database\Mysql;
use EasySwoole\Mysqli\Config;

/**
 * Class Client
 * @package EasySwoole\DatabaseMigrate\Databases
 * @author heelie.hj@gmail.com
 * @date 2021-03-30 09:43:32
 */
class Client
{
    use Singleton;

    private $config;

    /** @var $ */
    private $client = null;

    /**
     * @param Config $config
     * @author heelie.hj@gmail.com
     * @date 2021-03-30 09:42:39
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    public function getConfig(): ?Config
    {
        return $this->config;
    }

    /**
     * @return mixed|void
     */
    public function getClient(): \EasySwoole\Mysqli\Client
    {
        // if (!$this->client instanceof Mysql) {
        //     $this->client = new Mysql($this->getConfig());
        // }
        $this->client = new \EasySwoole\Mysqli\Client($this->getConfig());
        return $this->client;
    }
}
