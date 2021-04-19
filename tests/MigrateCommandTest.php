<?php

namespace EasySwoole\DatabaseMigrate\Tests;

use EasySwoole\Command\Caller;
use EasySwoole\Command\CommandManager;
use EasySwoole\DatabaseMigrate\Config\Config;
use EasySwoole\DatabaseMigrate\MigrateCommand;
use EasySwoole\DatabaseMigrate\MigrateManager;
use EasySwoole\DatabaseMigrate\Utility\Util;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;
use Swoole\Timer;
use function Swoole\Coroutine\run;

class MigrateCommandTest extends TestCase
{
    public function setUp(): void
    {
        $config = new Config();
        $config->setHost("mysql");
        $config->setPort(3306);
        $config->setUser("root");
        $config->setPassword("123456");
        $config->setDatabase("easyswoole");
        $config->setTimeout(5.0);
        $config->setCharset("utf8mb4");
        MigrateManager::getInstance($config);
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
        $caller    = new Caller();
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

        $closure = function () use ($tableName) {
            $sql = "SHOW TABLES LIKE '%{$tableName}%'";
            if (!MigrateManager::getInstance()->query($sql)) {
                $createSql = "
                CREATE TABLE IF NOT EXISTS `{$tableName}` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
                  `name` varchar(255) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
                MigrateManager::getInstance()->query($createSql);
            }
        };
        if (Coroutine::getCid() == -1) {
            Timer::clearAll();
            run($closure);
        } else {
            $closure();
        }

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
        $caller    = new Caller();
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

        $result  = "";
        $closure = function () use ($tableName, &$result) {
            $result = MigrateManager::getInstance()->query("SHOW TABLES LIKE '%{$tableName}%'");
        };
        if (Coroutine::getCid() == -1) {
            Timer::clearAll();
            run($closure);
        } else {
            $closure();
        }

        $this->assertEquals([], $result);
    }

    public function testRunCommand()
    {
        $tableName = "gen_test";
        $caller    = new Caller();
        $caller->setScript("easyswoole");
        $caller->setCommand("migrate");
        $caller->setParams([
            "easyswoole",
            "migrate",
            "run",
        ]);
        $this->initCommandManager();
        CommandManager::getInstance()->run($caller);

        $result  = "";
        $closure = function () use ($tableName, &$result) {
            $result = MigrateManager::getInstance()->query("SHOW TABLES LIKE '%{$tableName}%'");
        };
        if (Coroutine::getCid() == -1) {
            Timer::clearAll();
            run($closure);
        } else {
            $closure();
        }

        $this->assertGreaterThan(0, sizeof($result));
    }

    public function testRollbackCommand()
    {
        $tableName = "gen_test";
        $caller    = new Caller();
        $caller->setScript("easyswoole");
        $caller->setCommand("migrate");
        $caller->setParams([
            "easyswoole",
            "migrate",
            "rollback",
        ]);
        $this->initCommandManager();
        CommandManager::getInstance()->run($caller);

        $result  = "";
        $closure = function () use ($tableName, &$result) {
            $result = MigrateManager::getInstance()->query("SHOW TABLES LIKE '%{$tableName}%'");
        };
        if (Coroutine::getCid() == -1) {
            Timer::clearAll();
            run($closure);
        } else {
            $closure();
        }

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
