<?php

namespace EasySwoole\DatabaseMigrate\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use EasySwoole\DDL\Blueprint\Create\Table as CreateTable;
use EasySwoole\DDL\DDLBuilder;
use EasySwoole\DDL\Enum\Character;
use EasySwoole\DDL\Enum\Engine;
use EasySwoole\DatabaseMigrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\DatabaseMigrate\Command\Migrate\CreateCommand;
use EasySwoole\DatabaseMigrate\Command\Migrate\GenerateCommand;
use EasySwoole\DatabaseMigrate\Command\Migrate\ResetCommand;
use EasySwoole\DatabaseMigrate\Command\Migrate\RollbackCommand;
use EasySwoole\DatabaseMigrate\Command\Migrate\RunCommand;
use EasySwoole\DatabaseMigrate\Command\Migrate\SeedCommand;
use EasySwoole\DatabaseMigrate\Command\Migrate\StatusCommand;
use EasySwoole\DatabaseMigrate\Config\Config;
use EasySwoole\DatabaseMigrate\Databases\DatabaseFacade;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Throwable;

/**
 * Class MigrateCommand
 * @package EasySwoole\DatabaseMigrate\Command
 * @author heelie.hj@gmail.com
 * @date 2020/9/4 22:16:48
 */
class MigrateCommand extends CommandAbstract
{
    /** @var DatabaseFacade */
    protected $dbFacade;

    private $command = [
        'create'   => CreateCommand::class,
        'generate' => GenerateCommand::class,
        'reset'    => ResetCommand::class,
        'rollback' => RollbackCommand::class,
        'run'      => RunCommand::class,
        'seed'     => SeedCommand::class,
        'status'   => StatusCommand::class,
    ];

    public function commandName(): string
    {
        try {
            $option = $this->getArg(0);
            if (isset($this->command[$option])) {
                return $this->callOptionMethod($option, __FUNCTION__);
            }
        } catch (Throwable $throwable) {
        }
        return 'migrate';
    }

    public function desc(): string
    {
        try {
            $option = $this->getArg(0);
            if (isset($this->command[$option])) {
                return $this->callOptionMethod($option, __FUNCTION__);
            }
        } catch (Throwable $throwable) {
        }
        return 'database migrate tool';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        try {
            $option = $this->getArg(0);
            if (isset($this->command[$option])) {
                return $this->callOptionMethod($option, __FUNCTION__, [$commandHelp]);
            }
        } catch (Throwable $throwable) {
            //do something
        } finally {
            $commandHelp->addActionOpt('-m, --mode[=dev]', 'Run mode');
            $commandHelp->addActionOpt('-h, --help', 'Get help');
        }
        $commandHelp->addAction('create', 'Create the migration repository');
        $commandHelp->addAction('generate', 'Generate migration repository for existing tables');
        $commandHelp->addAction('run', 'run all migrations');
        $commandHelp->addAction('rollback', 'Rollback the last database migration');
        // $commandHelp->addAction('fresh', 'Drop all tables and re-run all migrations');
        // $commandHelp->addAction('refresh', 'Reset and re-run all migrations');
        $commandHelp->addAction('reset', 'Rollback all database migrations');
        $commandHelp->addAction('seed', 'Data filling tool');
        $commandHelp->addAction('status', 'Show the status of each migration');
        return $commandHelp;
    }

    public function exec(): ?string
    {
        try {
            $this->check();
            return $this->callOptionMethod($this->getArg(0), __FUNCTION__);
        } catch (Throwable $throwable) {
            return Color::error($throwable->getMessage()) . "\n" .
                CommandManager::getInstance()->displayCommandHelp('migrate');
        }
    }

    /**
     * @param $option
     * @param $method
     * @param $args
     * @return mixed
     * @throws ReflectionException
     */
    private function callOptionMethod($option, $method, $args = [])
    {
        if (!isset($this->command[$option])) {
            throw new InvalidArgumentException('Migration command error');
        }
        $ref = new ReflectionClass($this->command[$option]);
        return call_user_func([$ref->newInstance(), $method], ...$args);
    }

    private function check()
    {
        $this->dbFacade = DatabaseFacade::getInstance();
        $this->checkDefaultMigrateTable();
    }

    private function checkDefaultMigrateTable()
    {
        $tableExists = $this->dbFacade->query('SHOW TABLES LIKE "' . Config::DEFAULT_MIGRATE_TABLE . '"');
        if (empty($tableExists)) {
            $this->createDefaultMigrateTable();
        }
    }

    private function createDefaultMigrateTable()
    {
        $sql = DDLBuilder::create(Config::DEFAULT_MIGRATE_TABLE, function (CreateTable $table) {
            $table->setIfNotExists()->setTableAutoIncrement(1);
            $table->setTableEngine(Engine::INNODB);
            $table->setTableCharset(Character::UTF8MB4_GENERAL_CI);
            $table->int('id', 10)->setIsUnsigned()->setIsAutoIncrement()->setIsPrimaryKey();
            $table->varchar('migration', 255)->setColumnCharset(Character::UTF8MB4_GENERAL_CI)->setIsNotNull();
            $table->int('batch', 10)->setIsNotNull();
            $table->normal('ind_batch', 'batch');
        });
        if ($this->dbFacade->query($sql) === false) {
            throw new RuntimeException('Create default migrate table fail.' . PHP_EOL . ' SQL: ' . $sql);
        }
    }
}