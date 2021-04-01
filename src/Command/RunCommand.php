<?php

namespace EasySwoole\DatabaseMigrate\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\DDL\Blueprint\Create\Table as CreateTable;
use EasySwoole\DDL\DDLBuilder;
use EasySwoole\DDL\Enum\Character;
use EasySwoole\DDL\Enum\Engine;
use EasySwoole\DatabaseMigrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\DatabaseMigrate\MigrateCommand;
use EasySwoole\DatabaseMigrate\MigrateManager;
use EasySwoole\DatabaseMigrate\Utility\Util;
use EasySwoole\Spl\SplArray;
use RuntimeException;

/**
 * Class RunCommand
 * @package EasySwoole\DatabaseMigrate\Command\Migrate
 * @author heelie.hj@gmail.com
 * @date 2020/9/4 22:19:27
 */
final class RunCommand extends CommandAbstract
{
    private $dbClient;

    public function __construct()
    {
        $this->dbClient = MigrateManager::getInstance()->getClient();
    }

    public function commandName(): string
    {
        return 'migrate run';
    }

    public function desc(): string
    {
        return 'database migrate run';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        return $commandHelp;
    }

    /**
     * @return string|null
     */
    public function exec(): ?string
    {
        $waitMigrationFiles = $this->getMigrationFiles();
        if (empty($waitMigrationFiles)) {
            return Color::success('No tables need to be migrated.');
        }
        sort($waitMigrationFiles);

        $outMsg  = [];
        $batchNo = $this->getBatchNo();
        $client  = MigrateManager::getInstance()->getClient();
        $config  = MigrateManager::getInstance()->getConfig();
        foreach ($waitMigrationFiles as $file) {
            $outMsg[]  = "<brown>Migrating: </brown>{$file}";
            $startTime = microtime(true);
            $className = Util::migrateFileNameToClassName($file);
            try {
                $ref = new \ReflectionClass($className);
                $sql = call_user_func([$ref->newInstance(), 'up']);
                $client->queryBuilder()->raw($sql);
                if ($client->execBuilder()) {
                    $noteSql = 'INSERT INTO ' . $config->getMigrateTable() . ' (`migration`,`batch`) VALUE (\'' . $file . '\',\'' . $batchNo . '\')';
                    $client->queryBuilder()->raw($noteSql);
                    $client->execBuilder();
                }
            } catch (\Throwable $e) {
                return Color::error($e->getMessage());
            }
            $outMsg[] = "<green>Migrated:  </green>{$file} (" . round(microtime(true) - $startTime, 2) . " seconds)";
        }
        $outMsg[] = "<success>Migration table successfully.</success>";
        return Color::render(implode(PHP_EOL, $outMsg));
    }

    private function getMigrationFiles()
    {
        $allMigrationFiles = Util::getAllMigrateFiles();
        Util::requireOnce($allMigrationFiles);
        foreach ($allMigrationFiles as $key => $file) {
            $allMigrationFiles[$key] = basename($file, '.php');
        }
        $client = MigrateManager::getInstance()->getClient();
        $config = MigrateManager::getInstance()->getConfig();
        $client->queryBuilder()->raw('SELECT `migration` FROM ' . $config->getMigrateTable() . ' ORDER BY batch ASC,migration ASC');
        $alreadyMigrationFiles = $client->execBuilder();
        $alreadyMigrationFiles = array_column($alreadyMigrationFiles, 'migration');

        foreach ($allMigrationFiles as $key => $file) {
            if (in_array($file, $alreadyMigrationFiles)) {
                unset($allMigrationFiles[$key]);
                continue;
            }
        }
        return $allMigrationFiles;
    }

    /**
     * @return int
     */
    public function getBatchNo()
    {
        $client = MigrateManager::getInstance()->getClient();
        $config = MigrateManager::getInstance()->getConfig();
        $client->queryBuilder()->raw('select max(`batch`) as max_batch from ' . $config->getMigrateTable());
        $maxResult = $client->execBuilder();
        return intval($maxResult[0]['max_batch']) + 1;
    }

}
