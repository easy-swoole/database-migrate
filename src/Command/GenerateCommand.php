<?php

namespace EasySwoole\DatabaseMigrate\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\Color;
use EasySwoole\DatabaseMigrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\DatabaseMigrate\MigrateManager;
use EasySwoole\DatabaseMigrate\DDLSyntax\DDLColumnSyntax;
use EasySwoole\DatabaseMigrate\DDLSyntax\DDLForeignSyntax;
use EasySwoole\DatabaseMigrate\DDLSyntax\DDLIndexSyntax;
use EasySwoole\DatabaseMigrate\DDLSyntax\DDLTableSyntax;
use EasySwoole\DatabaseMigrate\Utility\Util;
use EasySwoole\Utility\File;
use RuntimeException;
use Exception;
use Throwable;

/**
 * Class GenerateCommand
 * @package EasySwoole\DatabaseMigrate\Command\Migrate
 * @author heelie.hj@gmail.com
 * @date 2020/9/19 00:24:58
 */
final class GenerateCommand extends CommandAbstract
{
    public function commandName(): string
    {
        return 'migrate generate';
    }

    public function desc(): string
    {
        return 'database migrate generate';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addActionOpt('-t, --table', 'Generate the migration repository of the specified table, multiple tables can be separated by ","');
        $commandHelp->addActionOpt('-i, --ignore', 'Tables that need to be excluded when generate the migration repository, multiple tables can be separated by ","');
        return $commandHelp;
    }

    /**
     * @return string|null
     * @throws Throwable
     * @throws \EasySwoole\Mysqli\Exception\Exception
     */
    public function exec(): ?string
    {
        // need to migrate
        $migrateTables = $this->getExistsTables();
        if ($specifiedTables = $this->getOpt(['t', 'table'])) {
            $specifiedTables = explode(',', $specifiedTables);
            array_walk($specifiedTables, function ($tableName) use ($migrateTables) {
                if (!in_array($tableName, $migrateTables)) {
                    throw new RuntimeException(sprintf('Table: "%s" not found.', $tableName));
                }
            });
            $migrateTables = $specifiedTables;
        }

        // ignore table
        $ignoreTables = $this->getIgnoreTables();
        $allTables = array_diff($migrateTables, $ignoreTables);
        if (empty($allTables)) {
            throw new RuntimeException('No table found.');
        }
        $batchNo = (new RunCommand)->getBatchNo();
        $outMsg = [];
        foreach ($allTables as $tableName) {
            $this->generate($tableName, $batchNo, $outMsg);
        }
        $outMsg[] = '<success>All table migration repository generation completed.</success>';
        return Color::render(join(PHP_EOL, $outMsg));
    }

    /**
     * @param $tableName
     * @param $batchNo
     * @param $outMsg
     * @throws Throwable
     * @throws \EasySwoole\Mysqli\Exception\Exception
     */
    private function generate($tableName, $batchNo, &$outMsg)
    {
        $config = MigrateManager::getInstance()->getConfig();
        $migrateClassName = 'Create' . ucfirst(Util::lineConvertHump($tableName));
        $migrateFileName  = Util::genMigrateFileName($migrateClassName);
        $migrateFilePath  = $config->getMigratePath() . $migrateFileName;

        $fileName  = basename($migrateFileName, '.php');
        $outMsg[]  = "<brown>Generating: </brown>{$fileName}";
        $startTime = microtime(true);

        $tableSchema     = $config->getDatabase();
        $createTableDDl  = str_replace(PHP_EOL,
            str_pad(PHP_EOL, strlen(PHP_EOL) + 12, ' ', STR_PAD_RIGHT),
            join(PHP_EOL, array_filter([
                    DDLTableSyntax::generate($tableSchema, $tableName),
                    DDLColumnSyntax::generate($tableSchema, $tableName),
                    DDLIndexSyntax::generate($tableSchema, $tableName),
                    DDLForeignSyntax::generate($tableSchema, $tableName),
                ])
            )
        );

        if (!File::touchFile($migrateFilePath, false)) {
            throw new Exception(sprintf('Migration file "%s" creation failed, file already exists or directory is not writable', $migrateFilePath));
        }

        $contents = str_replace(
            [
                $config->getMigrateTemplateClassName(),
                $config->getMigrateTemplateTableName(),
                $config->getMigrateTemplateDdlSyntax()
            ],
            [
                $migrateClassName,
                $tableName,
                $createTableDDl
            ],
            file_get_contents($config->getMigrateGenerateTemplate())
        );
        if (file_put_contents($migrateFilePath, $contents) === false) {
            throw new Exception(sprintf('Migration file "%s" is not writable', $migrateFilePath));
        }
        MigrateManager::getInstance()->insert($config->getMigrateTable(),
            [
                "migration" => $fileName,
                "batch" => $batchNo
            ]
        );
        $outMsg[] = "<green>Generated:  </green>{$fileName} (" . round(microtime(true) - $startTime, 2) . " seconds)";
    }

    /**
     * already exists tables
     *
     * @return array
     * @throws Throwable
     * @throws \EasySwoole\Mysqli\Exception\Exception
     */
    protected function getExistsTables(): array
    {
        $result = MigrateManager::getInstance()->query('SHOW TABLES;');
        if (empty($result)) {
            throw new RuntimeException('No table found.');
        }
        return array_map('current', $result);
    }

    /**
     * ignore tables
     *
     * @return array
     */
    protected function getIgnoreTables(): array
    {
        $config = MigrateManager::getInstance()->getConfig();
        $ignoreTables = [$config->getMigrateTable()];
        if ($ignore = $this->getOpt(['i', 'ignore'])) {
            return array_merge($ignoreTables, explode(',', $ignore));
        }
        return $ignoreTables;
    }
}
