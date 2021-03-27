<?php

namespace EasySwoole\DatabaseMigrate\Validate;

use EasySwoole\DatabaseMigrate\Utility\Util;

/**
 * Class Validator
 * @package EasySwoole\DatabaseMigrate\Validate
 * @author heelie.hj@gmail.com
 * @date 2020/8/22 21:29:54
 */
class Validator
{
    public static function isValidName(string $str): bool
    {
        return boolval(preg_match('/^(?!_|\d)\w+$/', $str));
    }

    public static function isHumpName(string $str): bool
    {
        return boolval(preg_match('/^([A-Z][a-z0-9]+)+$/', $str));
    }

    public static function validClass($className, $type)
    {
        $files = [];
        switch ($type) {
            case 'migrate':
                $files = Util::getAllMigrateFiles();
                break;
            case 'seeder':
                $files = Util::getAllSeederFiles();
                break;
        }

        Util::requireOnce($files);

        return class_exists($className);
    }
}