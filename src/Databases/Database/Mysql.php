<?php

namespace EasySwoole\DatabaseMigrate\Databases\Database;

use EasySwoole\Command\Color;
use EasySwoole\DatabaseMigrate\Databases\AbstractInterface\DatabaseInterface;
use EasySwoole\Spl\SplArray;
use mysqli;
use RuntimeException;

/**
 * Class Mysql
 * @package EasySwoole\DatabaseMigrate\Databases\Database
 * @author heelie.hj@gmail.com
 * @date 2020/8/22 21:21:35
 */
class Mysql implements DatabaseInterface
{
    /** @var mysqli */
    private $resource;

    public function connect(SplArray $config)
    {
        try {
            $this->resource = new mysqli($config->host, $config->username, $config->password, $config->database, $config->port ?: 3306);
        } catch (\Throwable $exception) {
            die(Color::error($exception->getMessage()) . PHP_EOL);
        }

        if ($this->resource->connect_error) {
            throw new RuntimeException('database connect error:' . $this->resource->connect_error);
        }
        $this->resource->query('SET NAMES UTF8');
        return $this;
    }

    public function query(string $query)
    {
        $result = $this->resource->query($query);
        if (is_bool($result)) {
            if ($result === false && $this->resource->error) {
                throw new RuntimeException($this->resource->error . PHP_EOL . ' SQL:' . $query);
            }
            return $result;
        } else {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    public function close()
    {
        $this->resource->close();
    }
}