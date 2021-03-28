<?php

namespace EasySwoole\DatabaseMigrate\Tests;

use EasySwoole\Command\Caller;
use EasySwoole\Command\CommandManager;
use EasySwoole\DatabaseMigrate\Command\MigrateCommand;
use EasySwoole\DatabaseMigrate\Databases\DatabaseFacade;
use EasySwoole\DatabaseMigrate\Utility\Util;
use EasySwoole\Spl\SplArray;
use PHPUnit\Framework\TestCase;

class MigrateCommandTest extends TestCase
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

    public function initCommandManager()
    {
        CommandManager::getInstance()->setArgs([]);
        CommandManager::getInstance()->setOpts([]);
        CommandManager::getInstance()->setDesc("");
        CommandManager::getInstance()->addCommand(new MigrateCommand());
    }

    public function unlinkAllMigrateFiles()
    {
        foreach (Util::getAllMigrateFiles() as $migrateFile) {
            @unlink($migrateFile);
        }
    }

    public function unlinkAllSeederFiles()
    {
        foreach (Util::getAllSeederFiles() as $seederFile) {
            @unlink($seederFile);
        }
    }

    public function testCreateCommand()
    {
        $tableName = "User";
        $caller = new Caller();
        $caller->setScript("easyswoole");
        $caller->setCommand("migrate");
        $caller->setParams([
            "easyswoole",
            "migrate",
            "create",
            "--create={$tableName}",
        ]);
        $this->unlinkAllMigrateFiles();
        $this->initCommandManager();
        CommandManager::getInstance()->run($caller);
        Util::requireOnce(Util::getAllMigrateFiles());
        $this->assertTrue(class_exists($tableName));
    }

    public function testGenerateCommand()
    {
        $tableName = "gen_test";
        DatabaseFacade::getInstance()->query("
        CREATE TABLE `{$tableName}` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
          `name` varchar(255) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $caller = new Caller();
        $caller->setScript("easyswoole");
        $caller->setCommand("migrate");
        $caller->setParams([
            "easyswoole",
            "migrate",
            "generate",
            "--tables={$tableName}",
        ]);
        $this->unlinkAllMigrateFiles();
        $this->initCommandManager();
        CommandManager::getInstance()->run($caller);
        Util::requireOnce(Util::getAllMigrateFiles());
        $this->assertTrue(class_exists("CreateGenTest"));
    }

    public function testResetCommand()
    {
        $tableName = "gen_test";
        $caller = new Caller();
        $caller->setScript("easyswoole");
        $caller->setCommand("migrate");
        $caller->setParams([
            "easyswoole",
            "migrate",
            "reset",
        ]);
        // $this->unlinkAllMigrateFiles();
        $this->initCommandManager();
        CommandManager::getInstance()->run($caller);
        $result = DatabaseFacade::getInstance()->query("SHOW TABLES LIKE '%{$tableName}%'");
        $this->assertEquals([], $result);
    }

    public function testRunCommand()
    {
        $tableName = "gen_test";
        $caller = new Caller();
        $caller->setScript("easyswoole");
        $caller->setCommand("migrate");
        $caller->setParams([
            "easyswoole",
            "migrate",
            "run",
        ]);
        $this->initCommandManager();
        CommandManager::getInstance()->run($caller);
        $result = DatabaseFacade::getInstance()->query("SHOW TABLES LIKE '%{$tableName}%'");
        $this->assertGreaterThan(0, sizeof($result));
    }

    public function testRollbackCommand()
    {
        $tableName = "gen_test";
        $caller = new Caller();
        $caller->setScript("easyswoole");
        $caller->setCommand("migrate");
        $caller->setParams([
            "easyswoole",
            "migrate",
            "rollback",
        ]);
        $this->initCommandManager();
        CommandManager::getInstance()->run($caller);
        $result = DatabaseFacade::getInstance()->query("SHOW TABLES LIKE '%{$tableName}%'");
        $this->assertEquals([], $result);
    }

    public function testStatusCommand()
    {
        $caller = new Caller();
        $caller->setScript("easyswoole");
        $caller->setCommand("migrate");
        $caller->setParams([
            "easyswoole",
            "migrate",
            "status",
        ]);
        $this->initCommandManager();
        CommandManager::getInstance()->run($caller);
        $this->assertTrue(true);
    }
}