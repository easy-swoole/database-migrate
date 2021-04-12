<?php

namespace EasySwoole\DatabaseMigrate\Tests;

use EasySwoole\Command\Caller;
use EasySwoole\Command\CommandManager;
use EasySwoole\DatabaseMigrate\Config\Config;
use EasySwoole\DatabaseMigrate\MigrateCommand;
use EasySwoole\DatabaseMigrate\MigrateManager;
use EasySwoole\DatabaseMigrate\Utility\Util;
use PHPUnit\Framework\TestCase;

class SeedCommandTest extends TestCase
{
    public function setUp(): void
    {
        $config = new Config();
        $config->setHost("mysql5");
        $config->setPort(3306);
        $config->setUser("root");
        $config->setPassword("123456");
        $config->setDatabase("easyswoole");
        $config->setTimeout(5.0);
        $config->setCharset("utf8mb4");
        MigrateManager::getInstance($config);
    }

    public function testSeedCommand()
    {
        $tableName = "SeederTest";
        $caller    = new Caller();
        $caller->setScript("easyswoole");
        $caller->setCommand("migrate");
        $caller->setParams([
            "easyswoole",
            "migrate",
            "seed",
            "--create={$tableName}"
        ]);
        CommandManager::getInstance()->setArgs([]);
        CommandManager::getInstance()->setOpts([]);
        CommandManager::getInstance()->setDesc("");
        CommandManager::getInstance()->addCommand(new MigrateCommand());
        CommandManager::getInstance()->run($caller);
        Util::requireOnce(Util::getAllSeederFiles());
        $this->assertTrue(class_exists("SeederTest"));
    }
}
