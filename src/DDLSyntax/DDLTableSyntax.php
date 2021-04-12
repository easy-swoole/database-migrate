<?php

namespace EasySwoole\DatabaseMigrate\DDLSyntax;


use EasySwoole\DatabaseMigrate\MigrateManager;
use EasySwoole\Mysqli\Exception\Exception;

/**
 * Class DDLTableSyntax
 * @package EasySwoole\DatabaseMigrate\DDLSyntax
 * @author heelie.hj@gmail.com
 * @date 2020/8/22 00:58:27
 */
class DDLTableSyntax
{
    /**
     * @param string $tableSchema
     * @param string $tableName
     * @return string
     * @throws Exception
     * @throws \Throwable
     */
    public static function generate(string $tableSchema, string $tableName): string
    {
        $tabAttrs = self::getTableAttribute($tableSchema, $tableName);
        return self::genTableDDLSyntax(current($tabAttrs));
    }

    /**
     * @param string $tableSchema
     * @param string $tableName
     * @return array
     * @throws Exception
     * @throws \Throwable
     */
    private static function getTableAttribute(string $tableSchema, string $tableName): array
    {
        $columns = join(',', [
            '`TABLE_NAME`',
            '`ENGINE`',
            '`TABLE_COLLATION`',
            '`AUTO_INCREMENT`',
            '`TABLE_COMMENT`',
        ]);
        $sql     = "SELECT {$columns}
                FROM `information_schema`.`TABLES` 
                WHERE `TABLE_SCHEMA`='{$tableSchema}' 
                AND `TABLE_NAME`='{$tableName}';";
        return MigrateManager::getInstance()->query($sql);
    }

    /**
     * @param array $table
     * @return string
     */
    private static function genTableDDLSyntax(array $table): string
    {
        $createTableDDl   = [];
        $createTableDDl[] = "\$table->setIfNotExists();";
        $createTableDDl[] = "\$table->setTableName('{$table['TABLE_NAME']}');";
        $createTableDDl[] = "\$table->setTableEngine('" . strtolower($table['ENGINE']) . "');";
        $createTableDDl[] = "\$table->setTableCharset('{$table['TABLE_COLLATION']}');";
        $createTableDDl[] = $table['AUTO_INCREMENT'] > 0 ? "\$table->setTableAutoIncrement(1);" : null;
        $createTableDDl[] = $table['TABLE_COMMENT'] ? "\$table->setTableComment('{$table['TABLE_COMMENT']}');" : null;
        return join(PHP_EOL, array_filter($createTableDDl));
    }
}
