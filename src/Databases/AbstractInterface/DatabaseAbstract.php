<?php

namespace EasySwoole\DatabaseMigrate\Databases\AbstractInterface;

use EasySwoole\DatabaseMigrate\Utility\CommandManager;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\Spl\SplArray;
use RuntimeException;

/**
 * Class DatabaseAbstract
 * @package EasySwoole\DatabaseMigrate\Databases\AbstractInterface
 * @author heelie.hj@gmail.com
 * @date 2020/8/22 21:26:20
 */
abstract class DatabaseAbstract
{
    /** @var SplArray */
    protected $config = null;

    protected $databases = [];

    public function getConfig()
    {
        if (is_null($this->config)) {
            $mode = CommandManager::getInstance()->getOpt(['m', 'mode']);
            if (!empty($mode)) {
                Core::getInstance()->runMode($mode);
            }
            Core::getInstance()->loadEnv();
            // $databaseConfig = Config::getInstance()->getConf('DATABASE');
            $databaseConfig = Config::getInstance()->getConf('MYSQL');
            if (!$databaseConfig) {
                throw new RuntimeException('Database configuration information was not read');
            }
            $this->setConfig(new SplArray($databaseConfig));
        }
        return $this->config;
    }

    public function setConfig(SplArray $config)
    {
        $this->config = $config;
    }

    abstract public function query(string $query);
}