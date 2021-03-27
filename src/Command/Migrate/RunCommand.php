<?php

namespace EasySwoole\DatabaseMigrate\Command\Migrate;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\DDL\Blueprint\Create\Table as CreateTable;
use EasySwoole\DDL\DDLBuilder;
use EasySwoole\DDL\Enum\Character;
use EasySwoole\DDL\Enum\Engine;
use EasySwoole\DatabaseMigrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\DatabaseMigrate\Command\MigrateCommand;
use EasySwoole\DatabaseMigrate\Config\Config;
use EasySwoole\DatabaseMigrate\Databases\DatabaseFacade;
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
    private $dbFacade;

    public function __construct()
    {
        $this->dbFacade = DatabaseFacade::getInstance();
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
        foreach ($waitMigrationFiles as $file) {
            $outMsg[]  = "<brown>Migrating: </brown>{$file}";
            $startTime = microtime(true);
            $className = Util::migrateFileNameToClassName($file);
            try {
                $ref = new \ReflectionClass($className);
                $sql = call_user_func([$ref->newInstance(), 'up']);
                if ($this->dbFacade->query($sql)) {
                    $noteSql = 'insert into ' . Config::DEFAULT_MIGRATE_TABLE . ' (`migration`,`batch`) VALUE (\'' . $file . '\',\'' . $batchNo . '\')';
                    $this->dbFacade->query($noteSql);
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
        $alreadyMigrationFiles = $this->dbFacade->query('select `migration` from ' . Config::DEFAULT_MIGRATE_TABLE . ' order by batch asc,migration asc');
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
        $maxResult = $this->dbFacade->query('select max(`batch`) as max_batch from ' . Config::DEFAULT_MIGRATE_TABLE);
        return intval($maxResult[0]['max_batch']) + 1;
    }

}