# db-migrate

参照Laravel开发的easyswoole数据库版本迁移工具

## 使用方法

在全局 `boostrap` 事件中注册 `MigrateCommand` 

> bootstrap.php

```php
\EasySwoole\Command\CommandManager::getInstance()->addCommand(new \EasySwoole\DatabaseMigrate\Command\MigrateCommand());
```

在dev中增加配置信息

> dev.php

```php
return [
    // ......
    'MYSQL' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'username' => 'root',
        'password' => 'root',
        'database' => 'easyswoole',
    ]
];
```

执行 `php easyswoole migrate -h`

```shell
php easyswoole migrate -h
Database migrate tool

Usage:
  easyswoole migrate ACTION [--opts ...]

Actions:
  create    Create the migration repository
  generate  Generate migration repository for existing tables
  run       run all migrations
  rollback  Rollback the last database migration
  reset     Rollback all database migrations
  seed      Data filling tool
  status    Show the status of each migration

Options:
  -h, --help  Get help
```

> `create`  

创建一个迁移模板

可用操作选项：

- `--alter`：创建一个修改表的迁移模板
  - 示例：`php easyswoole migrate create --alter=TableName`
- `--create`：创建一个建表的迁移模板
  - 示例：`php easyswoole migrate create --create=TableName`
- `--drop`：创建一个删表的迁移模板
  - 示例：`php easyswoole migrate create --drop=TableName`
- `--table`：创建一个基础迁移模板
  - 示例：`php easyswoole migrate create --table=TableName`  等同于 `php easyswoole migrate create TableName`

> `generate` 

对已存在的表生成适配当前迁移工具的迁移模板

可用操作选项：

- `--tables`：指定要生成迁移模板的表，多个表用 ',' 隔开
  - 示例：`php easyswoole migrate generate --tables=table1,table2`
- `--ignore`：指定要忽略生成迁移模板的表，多个表用 ',' 隔开
  - 示例：`php easyswoole migrate generate --ignore=table1,table2`

> run

迁移所有未迁移过的版本

> rollback

回滚迁移记录，默认回滚上一次的迁移，指定操作相关参数可以从status命令中查看

可用操作选项：

- `--batch`：指定要回滚的批次号 
  - 示例：`php easyswoole migrate rollback --batch=2`
- `--id`：指定要回滚的迁移ID
  - 示例：`php easyswoole migrate rollback --id=2`

> reset

回滚所有迁移记录

> seed

数据填充工具

可用操作选项：

- `--class`：指定要填充的class name，也就是文件名 ==（请保证填充工具文件名与类名完全相同）== 
  - 示例：`php easyswoole migrate seed --class=UserTable`
- `--create`：创建一个数据填充模板
  - 示例：`php easyswoole migrate seed --create=UserTable`

> status

迁移状态