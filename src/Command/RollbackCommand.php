<?php

namespace EasySwoole\DatabaseMigrate\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\DatabaseMigrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\DatabaseMigrate\MigrateCommand;
use EasySwoole\DatabaseMigrate\MigrateManager;
use EasySwoole\DatabaseMigrate\Utility\Util;
use Exception;

/**
 * Class RollbackCommand
 * @package EasySwoole\DatabaseMigrate\Command\Migrate
 * @author heelie.hj@gmail.com
 * @date 2020/9/19 00:30:42
 */
final class RollbackCommand extends CommandAbstract
{
    public function commandName(): string
    {
        return 'migrate rollback';
    }

    public function desc(): string
    {
        return 'database migrate rollback';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addActionOpt('-b, --batch', 'rollback migrate batch no');
        $commandHelp->addActionOpt('-i, --id', 'rollback migrate id');
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
            } catch (\Throwable $e) {
                return Color::error($e->getMessage());
            }
            $outMsg[] = "<green>Migrated:  </green>{$file} (" . round(microtime(true) - $startTime, 2) . " seconds)";
        }
        $outMsg[] = "<success>Migration table rollback successfully.</success>";
        return Color::render(implode(PHP_EOL, $outMsg));
    }

    private function getRollbackFiles()
    {
        $client    = MigrateManager::getInstance()->getClient();
        $config    = MigrateManager::getInstance()->getConfig();
        $tableName = $config->getMigrateTable();
        $sql       = "SELECT `id`,`migration` FROM `{$tableName}` WHERE ";
        if (($batch = $this->getOpt(['b', 'batch'])) && is_numeric($batch)) {
            $sql .= " `batch`={$batch} ";
        } elseif (($id = $this->getOpt(['i', 'id'])) && is_numeric($id)) {
            $sql .= " `id`={$id} ";
        } else {
            $sql .= " `batch`=(SELECT MAX(`batch`) FROM `{$tableName}` )";
        }
        $sql .= " order by id desc";
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
