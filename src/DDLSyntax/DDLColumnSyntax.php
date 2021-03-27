<?php

namespace EasySwoole\DatabaseMigrate\DDLSyntax;

use EasySwoole\DDL\Enum\DataType;
use EasySwoole\DatabaseMigrate\Databases\DatabaseFacade;

/**
 * Class DDLColumnSyntax
 * @package EasySwoole\DatabaseMigrate\DDLSyntax
 * @author heelie.hj@gmail.com
 * @date 2020/8/22 21:12:14
 */
class DDLColumnSyntax
{
    private static $integer = [
        DataType::INT,
        DataType::INTEGER,
        DataType::BIGINT,
        DataType::TINYINT,
        DataType::SMALLINT,
        DataType::MEDIUMINT,
    ];

    private static $decimal = [
        DataType::FLOAT,
        DataType::DOUBLE,
        DataType::DECIMAL,
    ];

    private static $date = [
        DataType::DATE,
        DataType::YEAR,
    ];

    private static $time = [
        DataType::TIME,
        DataType::DATETIME,
        DataType::TIMESTAMP,
    ];

    private static $character = [
        DataType::CHAR,
        DataType::VARCHAR,
    ];

    private static $text = [
        DataType::TEXT,
        DataType::TINYTEXT,
        DataType::MEDIUMTEXT,
        DataType::LONGTEXT,
    ];

    private static $blob = [
        DataType::BLOB,
        DataType::TINYBLOB,
        DataType::MEDIUMBLOB,
        DataType::LONGBLOB,
    ];

    /**
     * @param string $tableSchema
     * @param string $tableName
     * @return string
     */
    public static function generate(string $tableSchema, string $tableName)
    {
        $colAttrs  = self::getColumnAttribute($tableSchema, $tableName);
        $columnDDl = array_map([__CLASS__, 'genColumnDDLSyntax'], $colAttrs);
        return join(PHP_EOL, $columnDDl);
    }

    /**
     * @param string $tableSchema
     * @param string $tableName
     * @return array
     */
    private static function getColumnAttribute(string $tableSchema, string $tableName)
    {
        $columns = join(',', [
            '`COLUMN_NAME`',
            '`COLUMN_DEFAULT`',
            '`IS_NULLABLE`',
            '`DATA_TYPE`',
            '`CHARACTER_MAXIMUM_LENGTH`',
            '`NUMERIC_PRECISION`',
            '`NUMERIC_SCALE`',
            '`DATETIME_PRECISION`',
            '`COLLATION_NAME`',
            '`COLUMN_TYPE`',
            '`COLUMN_KEY`',
            '`EXTRA`',
            '`COLUMN_COMMENT`',
        ]);
        $sql     = "SELECT {$columns}
                FROM `information_schema`.`COLUMNS` 
                WHERE `TABLE_SCHEMA`='{$tableSchema}' 
                AND `TABLE_NAME`='{$tableName}';";
        return DatabaseFacade::getInstance()->query($sql);
    }

    private static function genColumnDDLSyntax($colAttrs)
    {
        // setColumnType
        // setColumnName
        // setColumnLimit
        if (in_array($colAttrs['DATA_TYPE'], self::$integer)) {
            if ($colAttrs['DATA_TYPE'] == 'integer') {
                $colAttrs['DATA_TYPE'] = 'int';
            }
            $ddlSyntax = "\$table->{$colAttrs['DATA_TYPE']}('{$colAttrs['COLUMN_NAME']}','{$colAttrs['NUMERIC_PRECISION']}')";
        } elseif (in_array($colAttrs['DATA_TYPE'], self::$decimal)) {
            $ddlSyntax = "\$table->{$colAttrs['DATA_TYPE']}('{$colAttrs['COLUMN_NAME']}','{$colAttrs['NUMERIC_PRECISION']}','{$colAttrs['NUMERIC_SCALE']}')";
        } elseif (in_array($colAttrs['DATA_TYPE'], self::$date)) {
            $ddlSyntax = "\$table->{$colAttrs['DATA_TYPE']}('{$colAttrs['COLUMN_NAME']}')";
        } elseif (in_array($colAttrs['DATA_TYPE'], self::$time)) {
            $ddlSyntax = "\$table->{$colAttrs['DATA_TYPE']}('{$colAttrs['COLUMN_NAME']}','{$colAttrs['DATETIME_PRECISION']}')";
        } elseif (in_array($colAttrs['DATA_TYPE'], self::$character)) {
            $ddlSyntax = "\$table->{$colAttrs['DATA_TYPE']}('{$colAttrs['COLUMN_NAME']}','{$colAttrs['CHARACTER_MAXIMUM_LENGTH']}')";
        } elseif (in_array($colAttrs['DATA_TYPE'], self::$text)) {
            $ddlSyntax = "\$table->{$colAttrs['DATA_TYPE']}('{$colAttrs['COLUMN_NAME']}')";
        } elseif (in_array($colAttrs['DATA_TYPE'], self::$blob)) {
            $ddlSyntax = "\$table->{$colAttrs['DATA_TYPE']}('{$colAttrs['COLUMN_NAME']}')";
        } else {
            return sprintf('// Todo::For some reason the field "%s" is not generated', $colAttrs['COLUMN_NAME']);
        }
        // setColumnCharset
        $ddlSyntax .= $colAttrs['COLLATION_NAME'] ? "->setColumnCharset('{$colAttrs['COLLATION_NAME']}')" : '';
        // setColumnComment
        $ddlSyntax .= $colAttrs['COLUMN_COMMENT'] ? "->setColumnComment('{$colAttrs['COLUMN_COMMENT']}')" : '';
        // setDefaultValue
        $ddlSyntax .= $colAttrs['COLUMN_DEFAULT'] ? "->setDefaultValue('{$colAttrs['COLUMN_DEFAULT']}')" : '';
        // setIsAutoIncrement
        $ddlSyntax .= (strtolower($colAttrs['EXTRA']) == 'auto_increment') ? "->setIsAutoIncrement()" : '';
        // setIsBinary
        // todo setIsBinary
        // setIsNotNull
        $ddlSyntax .= (strtoupper($colAttrs['IS_NULLABLE']) == 'NO') ? "->setIsNotNull()" : '';
        // setIsPrimaryKey  todo::改为index实现
        // $ddlSyntax .= ($colAttrs['COLUMN_KEY'] == 'PRI') ? "->setIsPrimaryKey(true)" : '';
        // setIsUnique
        // setIsUnsigned
        $ddlSyntax .= (strpos(strtolower($colAttrs['COLUMN_TYPE']), 'unsigned') !== false) ? "->setIsUnsigned()" : '';
        // setZeroFill
        $ddlSyntax .= (strpos(strtolower($colAttrs['COLUMN_TYPE']), 'zerofill') !== false) ? "->setZeroFill()" : '';
        return $ddlSyntax . ";";
    }
}
