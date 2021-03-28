<?php

namespace EasySwoole\DatabaseMigrate\Tests;

use EasySwoole\Command\Caller;
use EasySwoole\Command\CommandManager;
use EasySwoole\DatabaseMigrate\Command\MigrateCommand;
use EasySwoole\DatabaseMigrate\Databases\DatabaseFacade;
use EasySwoole\DatabaseMigrate\Utility\Util;
use EasySwoole\Spl\SplArray;
use PHPUnit\Framework\TestCase;

class SeedCommandTest extends TestCase
{
    public function setUp()
    {
        require_once "EasySwoole.php";
        defined("EASYSWOOLE_ROOT") or define("EASYSWOOLE_ROOT", dirname(__DIR__) . '/tests');
        DatabaseFacade::getInstance()->setConfig(new SplArray([
            'host' => 'mysql5',
            'port' => 3306,
            'username' => 'root',
            'password' => '123456',
            'database' => 'easyswoole',
        ]));
    }

    public function testSeedCommand()
    {
        $tableName = "SeederTest";
        $caller = new Caller();
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