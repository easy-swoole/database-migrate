<?php

namespace EasySwoole\DatabaseMigrate\Config;

class Config
{
    /** @var string default migrate table name */
    const DEFAULT_MIGRATE_TABLE = 'migrations';

    /** @var string migrate path */
    const MIGRATE_PATH = EASYSWOOLE_ROOT . '/Database/Migrates/';

    /** @var string migrate template file path */
    const MIGRATE_TEMPLATE = __DIR__ . '/../Resource/migrate._php';

    /** @var string migrate template class name */
    const MIGRATE_TEMPLATE_CLASS_NAME = 'MigratorClassName';

    /** @var string migrate template table name */
    const MIGRATE_TEMPLATE_TABLE_NAME = 'MigratorTableName';

    /** @var string create migrate template file path */
    const MIGRATE_CREATE_TEMPLATE = __DIR__ . '/../Resource/migrate_create._php';

    /** @var string alter migrate template file path */
    const MIGRATE_ALTER_TEMPLATE = __DIR__ . '/../Resource/migrate_alter._php';

    /** @var string drop migrate template file path */
    const MIGRATE_DROP_TEMPLATE = __DIR__ . '/../Resource/migrate_drop._php';


    /** @var string seeder path */
    const SEEDER_PATH = EASYSWOOLE_ROOT . '/Database/Seeds/';

    /** @var string seeder template class name */
    const SEEDER_TEMPLATE_CLASS_NAME = 'SeederClassName';

    /** @var string seeder template file path */
    const SEEDER_TEMPLATE = __DIR__ . '/../Resource/seeder._php';


    /** @var string migrate template class name */
    const MIGRATE_TEMPLATE_DDL_SYNTAX = 'DDLSyntax';

    /** @var string generate migrate template file path */
    const MIGRATE_GENERATE_TEMPLATE = __DIR__ . '/../Resource/migrate_generate._php';
}