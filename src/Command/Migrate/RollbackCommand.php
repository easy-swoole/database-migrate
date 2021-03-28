<?php

namespace EasySwoole\DatabaseMigrate\Command\Migrate;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\DatabaseMigrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\DatabaseMigrate\Command\MigrateCommand;
use EasySwoole\DatabaseMigrate\Config\Config;
use EasySwoole\DatabaseMigrate\Databases\DatabaseFacade;
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
    private $dbFacade;

    public function __construct()
    {
        $this->dbFacade = DatabaseFacade::getInstance();
    }

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

        foreach ($waitRollbackFiles as $id => $file) {
            $outMsg[]  = "<brown>Migrating: </brown>{$file}";
            $startTime = microtime(true);
            $className = Util::migrateFileNameToClassName($file);
            try {
                $ref = new \ReflectionClass($className);
                $sql = call_user_func([$ref->newInstance(), 'down']);
                if ($this->dbFacade->query($sql)) {
                    $deleteSql = "delete from `" . Config::DEFAULT_MIGRATE_TABLE . "` where `id`='{$id}' ";
                    $this->dbFacade->query($deleteSql);
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
        $tableName = Config::DEFAULT_MIGRATE_TABLE;
        $sql       = "select `id`,`migration` from `{$tableName}` where ";
        if (($batch = $this->getOpt(['b', 'batch'])) && is_numeric($batch)) {
            $sql .= " `batch`={$batch} ";
        } elseif (($id = $this->getOpt(['i', 'id'])) && is_numeric($id)) {
            $sql .= " `id`={$id} ";
        } else {
            $sql .= " `batch`=(select max(batch) from `{$tableName}` )";
        }
        $sql                .= " order by id desc";
        $readyRollbackFiles = $this->dbFacade->query($sql);
        if (empty($readyRollbackFiles)) {
            return Color::success('No files to be rollback.');
        }
        $readyRollbackFiles = array_column($readyRollbackFiles, 'migration', 'id');

        foreach ($readyRollbackFiles as $id => $file) {
            $file = Config::MIGRATE_PATH . $file . ".php";
            if (file_exists($file)) {
                Util::requireOnce($file);
            }
        }
        return $readyRollbackFiles;
    }

}
