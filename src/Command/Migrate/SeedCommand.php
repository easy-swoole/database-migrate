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
use EasySwoole\DatabaseMigrate\Validate\Validator;
use EasySwoole\Spl\SplArray;
use EasySwoole\Utility\File;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Class SeedCommand
 * @package EasySwoole\DatabaseMigrate\Command\Migrate
 * @author heelie.hj@gmail.com
 * @date 2020/9/19 00:30:36
 */
final class SeedCommand extends CommandAbstract
{
    public function commandName(): string
    {
        return 'migrate fill';
    }

    public function desc(): string
    {
        return 'database migrate data fill';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addActionOpt('-cs, --class', 'The class name for data filling');
        $commandHelp->addActionOpt('-cr, --create', 'Create a seeder template');
        return $commandHelp;
    }

    /**
     * @return string|null
     */
    public function exec(): ?string
    {
        try {
            if ($createClass = $this->getOpt(['cr', 'create'])) {
                return $this->create($createClass);
            }
            if ($class = $this->getOpt(['cs', 'class'])) {
                return $this->seederRun(explode(',', $class));
            }
            return $this->seederRun(Util::getAllSeederFiles());
        } catch (Throwable $throwable) {
            return Color::error($throwable->getMessage());
        }
    }

    private function create($className)
    {
        if (!Validator::isValidName($className)) {
            throw new InvalidArgumentException('The migrate table name can only consist of letters, numbers and underscores, and cannot start with numbers and underscore');
        }

        if (Validator::validClass($className, 'seeder')) {
            throw new InvalidArgumentException(sprintf('class "%s" already exists', $className));
        }

        $className = ucfirst(Util::lineConvertHump($className));

        $seederFilePath = Config::SEEDER_PATH . $className . '.php';

        if (!File::touchFile($seederFilePath, false)) {
            throw new RuntimeException(sprintf('seeder file "%s" create failed, file already exists or directory is not writable', $seederFilePath));
        }

        $contents = str_replace(Config::SEEDER_TEMPLATE_CLASS_NAME, $className, file_get_contents(Config::SEEDER_TEMPLATE));

        if (file_put_contents($seederFilePath, $contents) === false) {
            throw new RuntimeException(sprintf('Seeder file "%s" is not writable', $seederFilePath));
        }

        return Color::success(sprintf('Seeder file "%s" created successfully', $seederFilePath));
    }

    private function seederRun(array $waitSeedFiles)
    {
        $outMsg = [];
        array_walk($waitSeedFiles, function ($className) use (&$outMsg) {
            $className = pathinfo($className, PATHINFO_FILENAME);
            $filename  = $className . '.php';
            $filepath  = Config::SEEDER_PATH . $filename;
            $startTime = microtime(true);
            $outMsg[]  = "<brown>Seeding: </brown>" . $filename;
            if (!file_exists($filepath)) {
                return $outMsg[] = "<warning>Seeded:  </warning>" . sprintf('seeder file "%s" not found. go next.', $filename);
            }
            Util::requireOnce($filepath);
            try {
                $ref = new \ReflectionClass($className);
                call_user_func([$ref->newInstance(), 'run']);
                return $outMsg[] = "<green>Seeded: </green>{$filename} (" . round(microtime(true) - $startTime, 2) . " seconds)";
            } catch (\Throwable $e) {
                return $outMsg[] = "<warning>Seeded: </warning>" . sprintf('seeder file "%s" error: %s', $filename, $e->getMessage());
            }
        });

        return Color::render(join(PHP_EOL, $outMsg));
    }
}