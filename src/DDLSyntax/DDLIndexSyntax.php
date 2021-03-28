<?php

namespace EasySwoole\DatabaseMigrate\DDLSyntax;

use EasySwoole\DatabaseMigrate\Databases\DatabaseFacade;
use EasySwoole\DatabaseMigrate\Utility\Util;

/**
 * Class DDLIndexSyntax
 * @package EasySwoole\DatabaseMigrate\DDLSyntax
 * @author heelie.hj@gmail.com
 * @date 2020/8/24 23:49:54
 */
class DDLIndexSyntax
{
    /**
     * @param string $tableSchema
     * @param string $tableName
     * @return string
     */
    public static function generate(string $tableSchema, string $tableName)
    {
        $indAttrs = self::getIndexAttribute($tableSchema, $tableName);
        $indAttrs = Util::arrayBindKey($indAttrs, 'INDEX_NAME');
        $indexDDl = array_map([__CLASS__, 'genIndexDDLSyntax'], $indAttrs);
        return join(PHP_EOL, $indexDDl);
    }

    private static function getIndexAttribute(string $tableSchema, string $tableName)
    {
        $columns = join(',', [
            '`NON_UNIQUE`',
            '`INDEX_NAME`',
            '`COLUMN_NAME`',
            '`INDEX_TYPE`',
            '`INDEX_COMMENT`',
        ]);
        $sql     = "SELECT {$columns}
                FROM `information_schema`.`STATISTICS` 
                WHERE `TABLE_SCHEMA`='{$tableSchema}' 
                AND `TABLE_NAME`='{$tableName}';";
        return DatabaseFacade::getInstance()->query($sql);
    }

    private static function genIndexDDLSyntax($indAttrs)
    {
        $nonUnique    = current($indAttrs)['NON_UNIQUE'];
        $indexName    = current($indAttrs)['INDEX_NAME'];
        $columnName   = array_column($indAttrs, 'COLUMN_NAME');
        $indexType    = current($indAttrs)['INDEX_TYPE'];
        $indexComment = current($indAttrs)['INDEX_COMMENT'];
        if ($indexName == 'PRIMARY') {
            $ddlSyntax = "\$table->primary";
        } elseif ($nonUnique === '0') {
            $ddlSyntax = "\$table->unique";
        } elseif ($indexType == 'FULLTEXT') {
            $ddlSyntax = "\$table->fulltext";
        } else {
            $ddlSyntax = "\$table->normal";
        }
        $ddlSyntax .= "('{$indexName}', ['" . join('\', \'', $columnName) . "'])";
        $ddlSyntax .= $indexComment ? "->setIndexComment('{$indexComment}')" : '';
        return $ddlSyntax . ';';
    }
}
