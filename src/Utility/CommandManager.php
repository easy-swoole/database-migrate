<?php

namespace EasySwoole\DatabaseMigrate\Utility;

use EasySwoole\Component\Singleton;
use EasySwoole\Command\CommandManager as BaseCommandManager;

/**
 * Class CommandManager
 * @package EasySwoole\DatabaseMigrate\Utility
 * @author heelie.hj@gmail.com
 * @date 2020/9/18 18:53:24
 */
class CommandManager
{
    use Singleton;

    /**
     * @param array|string|int $name
     * @param mixed|null $default
     * @return mixed|null
     */
    public function getArg($name, $default = null)
    {
        if (!is_array($name)) {
            return BaseCommandManager::getInstance()->getArg($name, $default);
        }
        foreach ($name as $item) {
            if ($arg = BaseCommandManager::getInstance()->getArg($item)) {
                return $arg;
            }
        }
        return $default;
    }

    /**
     * @param array|string|int $name
     * @param mixed|null $default
     * @return mixed|null
     */
    public function getOpt($name, $default = null)
    {
        if (!is_array($name)) {
            return BaseCommandManager::getInstance()->getOpt($name, $default);
        }
        foreach ($name as $item) {
            if ($opt = BaseCommandManager::getInstance()->getOpt($item)) {
                return $opt;
            }
        }
        return $default;
    }
}