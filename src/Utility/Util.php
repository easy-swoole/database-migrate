<?php

namespace EasySwoole\DatabaseMigrate\Utility;

use DateTime;
use DateTimeZone;
use EasySwoole\DatabaseMigrate\Config\Config;
use EasySwoole\DatabaseMigrate\Validate\Validator;
use Throwable;

/**
 * Class Util
 * @package EasySwoole\DatabaseMigrate\Utility
 * @author heelie.hj@gmail.com
 * @date 2020/8/22 21:28:02
 */
class Util
{
    /**
     * @param string $str
     * @return string
     */
    public static function lineConvertHump(string $str): string
    {
        return ucfirst(preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $str));
    }

    /**
     * @param string $str
     * @return string
     */
    public static function humpConvertLine(string $str): string
    {
        return ltrim(preg_replace_callback('/([A-Z]{1})/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, str_replace('_', '', $str)), '_');
    }

    /**
     * @param array $array
     * @param string $indexKey
     * @return array
     */
    public static function arrayBindKey(array $array, string $indexKey)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (isset($value[$indexKey])) {
                $result[$value[$indexKey]][] = $value;
            }
        }
        return $result;
    }

    /**
     * @param $migrateName
     * @return string
     * @throws Throwable
     */
    public static function genMigrateFileName($migrateName)
    {
        if (Validator::isHumpName($migrateName)) {
            $migrateName = self::humpConvertLine($migrateName);
        }
        return self::getCurrentMigrateDate() . '_' . $migrateName . '.php';
    }

    /**
     * @return string
     * @throws Throwable
     */
    public static function getCurrentMigrateDate()
    {
        return (new DateTime('now', new DateTimeZone('UTC')))->format('Y_m_d_His');
    }

    /**
     * @param $fileName
     * @return string
     */
    public static function migrateFileNameToClassName($fileName)
    {
        $withoutDateFileName = implode('_', array_slice(explode('_', $fileName), 4));
        return self::lineConvertHump(pathinfo($withoutDateFileName, PATHINFO_FILENAME));
    }

    /**
     * @return array
     */
    public static function getAllMigrateFiles(): array
    {
        return glob(Config::MIGRATE_PATH . '*.php');
    }

    /**
     * @return array
     */
    public static function getAllSeederFiles(): array
    {
        return glob(Config::SEEDER_PATH . '*.php');
    }

    /**
     * @param $files
     */
    public static function requireOnce($files)
    {
        foreach ((array)$files as $file) {
            require_once $file;
        }
    }
}