<?php

namespace EasySwoole\DatabaseMigrate\Command\Migrate;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Color;
use EasySwoole\DatabaseMigrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\DatabaseMigrate\Command\MigrateCommand;
use EasySwoole\DatabaseMigrate\Config\Config;
use EasySwoole\DatabaseMigrate\Utility\Util;
use EasySwoole\DatabaseMigrate\Validate\Validator;
use EasySwoole\Utility\File;
use Exception;
use InvalidArgumentException;
use Throwable;

/**
 * Class CreateCommand
 * @package EasySwoole\DatabaseMigrate\Command\Migrate
 * @author heelie.hj@gmail.com
 * @date 2020/9/19 00:30:50
 */
final class CreateCommand extends CommandAbstract
{
    public function commandName(): string
    {
        return 'migrate create';
    }

    public function desc(): string
    {
        return 'database migrate create';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addActionOpt('-a, --alter', 'generate alter migrate template');
        $commandHelp->addActionOpt('-c, --create', 'generate create migrate template');
        $commandHelp->addActionOpt('-d, --drop', 'generate drop migrate template');
        $commandHelp->addActionOpt('-t, --table', 'generate basic migrate template');
        return $commandHelp;
    }

    /**
     * @return string|null
     * @throws Throwable
     */
    public function exec(): ?string
    {
        [$migrateName, $migrateTemplate] = $this->getMigrateName();

        if (empty($migrateName)) {
            throw new InvalidArgumentException('Wrong number of parameters. Hope to get a parameter of migrate name');
        }

        $migrateClassName = $this->checkName($migrateName);

        $migrateFileName = Util::genMigrateFileName($migrateName);

        $migrateFilePath = Config::MIGRATE_PATH . $migrateFileName;

        // if (!File::createDirectory($migratePath)) {
        //     throw new \Exception(sprintf('Failed to create directory "%s", please check permissions', $migratePath));
        // }

        if (!File::touchFile($migrateFilePath, false)) {
            throw new Exception(sprintf('Migration file "%s" creation failed, file already exists or directory is not writable', $migrateFilePath));
        }

        $contents = str_replace([Config::MIGRATE_TEMPLATE_CLASS_NAME, Config::MIGRATE_TEMPLATE_TABLE_NAME], $migrateClassName, file_get_contents($migrateTemplate));

        if (file_put_contents($migrateFilePath, $contents) === false) {
            throw new Exception(sprintf('Migration file "%s" is not writable', $migrateFilePath));
        }

        return Color::success(sprintf('Migration file "%s" created successfully', $migrateFilePath));
    }

    private function getMigrateName()
    {
        if ($migrateName = $this->getOpt(['c', 'create'])) {
            return [$migrateName, Config::MIGRATE_CREATE_TEMPLATE];
        } elseif ($migrateName = $this->getOpt(['a', 'alter'])) {
            return [$migrateName, Config::MIGRATE_ALTER_TEMPLATE];
        } elseif ($migrateName = $this->getOpt(['d', 'drop'])) {
            return [$migrateName, Config::MIGRATE_DROP_TEMPLATE];
        } elseif ($migrateName = $this->getOpt(['t', 'table'])) {
            return [$migrateName, Config::MIGRATE_TEMPLATE];
        } elseif ($migrateName = $this->getArg(1)) {
            return [$migrateName, Config::MIGRATE_TEMPLATE];
        }
        return [null, null];
    }

    private function checkName($migrateName)
    {
        if (!Validator::isValidName($migrateName)) {
            throw new InvalidArgumentException('The migrate table name can only consist of letters, numbers and underscores, and cannot start with numbers and underscore');
        }

        if (!Validator::isHumpName($migrateName)) {
            $migrateName = Util::lineConvertHump($migrateName);
        }

        if (strpos($migrateName, '_') === false) {
            $migrateName = ucfirst($migrateName);
        }

        if (Validator::validClass($migrateName, 'migrate')) {
            throw new InvalidArgumentException(sprintf('class "%s" already exists', $migrateName));
        }

        return $migrateName;
    }
}
