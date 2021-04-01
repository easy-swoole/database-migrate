<?php

namespace EasySwoole\DatabaseMigrate\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use EasySwoole\DatabaseMigrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\DatabaseMigrate\MigrateCommand;
use EasySwoole\DatabaseMigrate\MigrateManager;
use EasySwoole\DatabaseMigrate\Utility\Util;
use Exception;
use Throwable;

/**
 * Class ResetCommand
 * @package EasySwoole\DatabaseMigrate\Command\Migrate
 * @author heelie.hj@gmail.com
 * @date 2020/9/19 00:25:14
 */
final class ResetCommand extends CommandAbstract
{
    public function commandName(): string
    {
        return 'migrate reset';
    }

    public function desc(): string
    {
        return 'database migrate reset';
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
        $waitRollbackFiles = $this->getRollbackFiles();

        $outMsg = [];

        $client = MigrateManager::getInstance()->getClient();
        $config = MigrateManager::getInstance()->getConfig();

        foreach ($waitRollbackFiles as $id => $file) {
            $outMsg[]  = "<brown>Migrating: </brown>{$file}";
            $startTime = microtime(true);
            $className = Util::migrateFileNameToClassName($file);
            try {
                $ref = new \ReflectionClass($className);
                $sql = call_user_func([$ref->newInstance(), 'down']);
                $client->queryBuilder()->raw($sql);
                if ($client->execBuilder()) {
                    $deleteSql = "DELETE FROM `" . $config->getMigrateTable() . "` WHERE `id`='{$id}' ";
                    $client->queryBuilder()->raw($deleteSql);
                    $client->execBuilder();
                }
            } catch (Throwable $e) {
                return Color::error($e->getMessage());
            }
            $outMsg[] = "<green>Migrated:  </green>{$file} (" . round(microtime(true) - $startTime, 2) . " seconds)";
        }
        $outMsg[] = "<success>Migration table rollback successfully.</success>";
        return Color::render(implode(PHP_EOL, $outMsg));
    }

    private function getRollbackFiles()
    {
        $config = MigrateManager::getInstance()->getConfig();
        $client    = MigrateManager::getInstance()->getClient();
        $tableName = $config->getMigrateTable();
        $sql       = "SELECT `id`,`migration` FROM `{$tableName}` ORDER BY `id` DESC ";
        $client->queryBuilder()->raw($sql);
        $readyRollbackFiles = $client->execBuilder();
        if (empty($readyRollbackFiles)) {
            return Color::success('No files to be rollback.');
        }
        $readyRollbackFiles = array_column($readyRollbackFiles, 'migration', 'id');

        foreach ($readyRollbackFiles as $id => $file) {
            $file = $config->getMigratePath() . $file . ".php";
            if (file_exists($file)) {
                Util::requireOnce($file);
            }
        }
        return $readyRollbackFiles;
    }

}
