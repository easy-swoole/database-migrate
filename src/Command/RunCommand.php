<?php

namespace EasySwoole\DatabaseMigrate\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\Color;
use EasySwoole\DatabaseMigrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\DatabaseMigrate\MigrateManager;
use EasySwoole\DatabaseMigrate\Utility\Util;

/**
 * Class RunCommand
 * @package EasySwoole\DatabaseMigrate\Command\Migrate
 * @author heelie.hj@gmail.com
 * @date 2020/9/4 22:19:27
 */
final class RunCommand extends CommandAbstract
{
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
        $config  = MigrateManager::getInstance()->getConfig();
        foreach ($waitMigrationFiles as $file) {
            $outMsg[]  = "<brown>Migrating: </brown>{$file}";
            $startTime = microtime(true);
            $className = Util::migrateFileNameToClassName($file);
            $ref = new \ReflectionClass($className);
            $sql = call_user_func([$ref->newInstance(), 'up']);
            if ($sql && MigrateManager::getInstance()->query($sql)) {
                MigrateManager::getInstance()->insert($config->getMigrateTable(),
                    [
                        "migration" => $file,
                        "batch" => $batchNo
                    ]
                );
            }
            $outMsg[] = "<green>Migrated:  </green>{$file} (" . round(microtime(true) - $startTime, 2) . " seconds)";
        }
        $outMsg[] = "<success>Migration table successfully.</success>";
        return Color::render(implode(PHP_EOL, $outMsg));
    }

    /**
     * @return array
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \Throwable
     */
    private function getMigrationFiles(): array
    {
        $allMigrationFiles = Util::getAllMigrateFiles();
        Util::requireOnce($allMigrationFiles);
        foreach ($allMigrationFiles as $key => $file) {
            $allMigrationFiles[$key] = basename($file, '.php');
        }
        $config = MigrateManager::getInstance()->getConfig();
        $sql = 'SELECT `migration` FROM ' . $config->getMigrateTable() . ' ORDER BY batch ASC,migration ASC';
        $alreadyMigrationFiles = MigrateManager::getInstance()->query($sql);
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
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \Throwable
     */
    public function getBatchNo(): int
    {
        $config = MigrateManager::getInstance()->getConfig();
        $sql = 'select max(`batch`) as max_batch from ' . $config->getMigrateTable();
        $maxResult = MigrateManager::getInstance()->query($sql);
        return intval($maxResult[0]['max_batch']) + 1;
    }

}
