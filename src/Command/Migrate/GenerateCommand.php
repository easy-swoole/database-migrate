<?php

namespace EasySwoole\DatabaseMigrate\Command\Migrate;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\DatabaseMigrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\DatabaseMigrate\Command\MigrateCommand;
use EasySwoole\DatabaseMigrate\Config\Config;
use EasySwoole\DatabaseMigrate\Databases\DatabaseFacade;
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
     */
    public function exec(): ?string
    {
        try {
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
            $allTables    = array_diff($migrateTables, $ignoreTables);
            if (empty($allTables)) {
                throw new RuntimeException('No table found.');
            }
            $batchNo = (new RunCommand)->getBatchNo();
            $outMsg  = [];
            foreach ($allTables as $tableName) {
                $this->generate($tableName, $batchNo, $outMsg);
            }
        } catch (Throwable $throwable) {
            return Color::error($throwable->getMessage());
        }
        $outMsg[] = '<success>All table migration repository generation completed.</success>';
        return Color::render(join(PHP_EOL, $outMsg));
    }

    private function generate($tableName, $batchNo, &$outMsg)
    {
        $migrateClassName = 'Create' . ucfirst(Util::lineConvertHump($tableName));
        $migrateFileName  = Util::genMigrateFileName($migrateClassName);
        $migrateFilePath  = Config::MIGRATE_PATH . $migrateFileName;

        $fileName  = basename($migrateFileName, '.php');
        $outMsg[]  = "<brown>Generating: </brown>{$fileName}";
        $startTime = microtime(true);

        // $defaultSqlDrive = DatabaseFacade::getInstance()->getConfig()->get('default');
        // $tableSchema     = DatabaseFacade::getInstance()->getConfig()->get($defaultSqlDrive . '.dbname');
        $tableSchema     = DatabaseFacade::getInstance()->getConfig()->get('database');
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
                Config::MIGRATE_TEMPLATE_CLASS_NAME,
                Config::MIGRATE_TEMPLATE_TABLE_NAME,
                Config::MIGRATE_TEMPLATE_DDL_SYNTAX
            ],
            [
                $migrateClassName,
                $tableName,
                $createTableDDl
            ],
            file_get_contents(Config::MIGRATE_GENERATE_TEMPLATE)
        );
        if (file_put_contents($migrateFilePath, $contents) === false) {
            throw new Exception(sprintf('Migration file "%s" is not writable', $migrateFilePath));
        }
        $noteSql = 'insert into ' . Config::DEFAULT_MIGRATE_TABLE . ' (`migration`,`batch`) VALUE (\'' . $fileName . '\',\'' . $batchNo . '\')';
        DatabaseFacade::getInstance()->query($noteSql);
        $outMsg[] = "<green>Generated:  </green>{$fileName} (" . round(microtime(true) - $startTime, 2) . " seconds)";
    }

    /**
     * already exists tables
     *
     * @return array
     */
    protected function getExistsTables()
    {
        $result = DatabaseFacade::getInstance()->query('SHOW TABLES;');
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
    protected function getIgnoreTables()
    {
        $ignoreTables = [Config::DEFAULT_MIGRATE_TABLE];
        if ($ignore = $this->getOpt(['i', 'ignore'])) {
            return array_merge($ignoreTables, explode(',', $ignore));
        }
        return $ignoreTables;
    }
}