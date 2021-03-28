<?php

namespace EasySwoole\DatabaseMigrate\Databases;

use EasySwoole\Component\Singleton;
use EasySwoole\DatabaseMigrate\Databases\AbstractInterface\DatabaseAbstract;
use EasySwoole\DatabaseMigrate\Databases\AbstractInterface\DatabaseInterface;
use EasySwoole\DatabaseMigrate\Databases\Database\Mysql;
use EasySwoole\Spl\SplArray;
use ReflectionClass;
use RuntimeException;
use Throwable;

/**
 * Database Facade
 * Class DatabaseFacade
 * @package EasySwoole\DatabaseMigrate\Databases
 * @author heelie.hj@gmail.com
 * @date 2020/06/30 15:56:21
 */
class DatabaseFacade extends DatabaseAbstract
{
    use Singleton;

    /**
     * @var DatabaseInterface
     */
    private static $database;

    /** @var SplArray|null */
    protected $config = null;

    /**
     * @var string[]
     */
    protected $databases = [
        'mysql' => Mysql::class
    ];

    private function check()
    {
        /** get default database type */
        $default = $this->getConfig()->get('default');
        if (!isset($this->databases[$default])) {
            throw new RuntimeException(sprintf('This database "%s" is not supported', $default));
        }
    }

    /**
     * @param string $query
     * @return mixed|void
     */
    public function query(string $query)
    {
        // todo::多数据库支持check
        // $this->check();
        // $default = $this->config->get('default');
        $this->getConfig();
        if (!static::$database instanceof DatabaseInterface) {
            static::$database = new $this->databases['mysql'];
        }
        static::$database->connect(new SplArray($this->config));
        return call_user_func([static::$database, __FUNCTION__], $query);
    }
}