<?php

namespace EasySwoole\DatabaseMigrate\Config;

class Config extends \EasySwoole\Mysqli\Config
{
    /** @var string default migrate table name */
    const DEFAULT_MIGRATE_TABLE = 'migrations';
    protected $migrate_table = "migrations";

    /** @var string migrate path */
    const MIGRATE_PATH = EASYSWOOLE_ROOT . '/Database/Migrates/';
    protected $migrate_path = EASYSWOOLE_ROOT . '/Database/Migrates/';

    /** @var string migrate template file path */
    const MIGRATE_TEMPLATE = __DIR__ . '/../Resource/migrate._php';
    protected $migrate_template = __DIR__ . '/../Resource/migrate._php';

    /** @var string migrate template class name */
    const MIGRATE_TEMPLATE_CLASS_NAME = 'MigratorClassName';
    protected $migrate_template_class_name = 'MigratorClassName';

    /** @var string migrate template table name */
    const MIGRATE_TEMPLATE_TABLE_NAME = 'MigratorTableName';
    protected $migrate_template_table_name = 'MigratorTableName';

    /** @var string create migrate template file path */
    const MIGRATE_CREATE_TEMPLATE = __DIR__ . '/../Resource/migrate_create._php';
    protected $migrate_create_template = __DIR__ . '/../Resource/migrate_create._php';

    /** @var string alter migrate template file path */
    const MIGRATE_ALTER_TEMPLATE = __DIR__ . '/../Resource/migrate_alter._php';
    protected $migrate_alter_template = __DIR__ . '/../Resource/migrate_alter._php';

    /** @var string drop migrate template file path */
    const MIGRATE_DROP_TEMPLATE = __DIR__ . '/../Resource/migrate_drop._php';
    protected $migrate_drop_template = __DIR__ . '/../Resource/migrate_drop._php';


    /** @var string seeder path */
    const SEEDER_PATH = EASYSWOOLE_ROOT . '/Database/Seeds/';
    protected $seeder_path = EASYSWOOLE_ROOT . '/Database/Seeds/';

    /** @var string seeder template class name */
    const SEEDER_TEMPLATE_CLASS_NAME = 'SeederClassName';
    protected $seeder_template_class_name = 'SeederClassName';

    /** @var string seeder template file path */
    const SEEDER_TEMPLATE = __DIR__ . '/../Resource/seeder._php';
    protected $seeder_template = __DIR__ . '/../Resource/seeder._php';


    /** @var string migrate template class name */
    const MIGRATE_TEMPLATE_DDL_SYNTAX = 'DDLSyntax';
    protected $migrate_template_ddl_syntax = 'DDLSyntax';

    /** @var string generate migrate template file path */
    const MIGRATE_GENERATE_TEMPLATE = __DIR__ . '/../Resource/migrate_generate._php';
    protected $migrate_generate_template = __DIR__ . '/../Resource/migrate_generate._php';


    /**
     * @return string
     */
    public function getMigrateTable(): string
    {
        return $this->migrate_table;
    }

    /**
     * @param string $migrate_table
     */
    public function setMigrateTable(string $migrate_table): void
    {
        $this->migrate_table = $migrate_table;
    }

    /**
     * @return string
     */
    public function getMigratePath(): string
    {
        return $this->migrate_path;
    }

    /**
     * @param string $migrate_path
     */
    public function setMigratePath(string $migrate_path): void
    {
        $this->migrate_path = $migrate_path;
    }

    /**
     * @return string
     */
    public function getMigrateTemplate(): string
    {
        return $this->migrate_template;
    }

    /**
     * @param string $migrate_template
     */
    public function setMigrateTemplate(string $migrate_template): void
    {
        $this->migrate_template = $migrate_template;
    }

    /**
     * @return string
     */
    public function getMigrateTemplateClassName(): string
    {
        return $this->migrate_template_class_name;
    }

    /**
     * @param string $migrate_template_class_name
     */
    public function setMigrateTemplateClassName(string $migrate_template_class_name): void
    {
        $this->migrate_template_class_name = $migrate_template_class_name;
    }

    /**
     * @return string
     */
    public function getMigrateTemplateTableName(): string
    {
        return $this->migrate_template_table_name;
    }

    /**
     * @param string $migrate_template_table_name
     */
    public function setMigrateTemplateTableName(string $migrate_template_table_name): void
    {
        $this->migrate_template_table_name = $migrate_template_table_name;
    }

    /**
     * @return string
     */
    public function getMigrateCreateTemplate(): string
    {
        return $this->migrate_create_template;
    }

    /**
     * @param string $migrate_create_template
     */
    public function setMigrateCreateTemplate(string $migrate_create_template): void
    {
        $this->migrate_create_template = $migrate_create_template;
    }

    /**
     * @return string
     */
    public function getMigrateAlterTemplate(): string
    {
        return $this->migrate_alter_template;
    }

    /**
     * @param string $migrate_alter_template
     */
    public function setMigrateAlterTemplate(string $migrate_alter_template): void
    {
        $this->migrate_alter_template = $migrate_alter_template;
    }

    /**
     * @return string
     */
    public function getMigrateDropTemplate(): string
    {
        return $this->migrate_drop_template;
    }

    /**
     * @param string $migrate_drop_template
     */
    public function setMigrateDropTemplate(string $migrate_drop_template): void
    {
        $this->migrate_drop_template = $migrate_drop_template;
    }

    /**
     * @return string
     */
    public function getSeederPath(): string
    {
        return $this->seeder_path;
    }

    /**
     * @param string $seeder_path
     */
    public function setSeederPath(string $seeder_path): void
    {
        $this->seeder_path = $seeder_path;
    }

    /**
     * @return string
     */
    public function getSeederTemplateClassName(): string
    {
        return $this->seeder_template_class_name;
    }

    /**
     * @param string $seeder_template_class_name
     */
    public function setSeederTemplateClassName(string $seeder_template_class_name): void
    {
        $this->seeder_template_class_name = $seeder_template_class_name;
    }

    /**
     * @return string
     */
    public function getSeederTemplate(): string
    {
        return $this->seeder_template;
    }

    /**
     * @param string $seeder_template
     */
    public function setSeederTemplate(string $seeder_template): void
    {
        $this->seeder_template = $seeder_template;
    }

    /**
     * @return string
     */
    public function getMigrateTemplateDdlSyntax(): string
    {
        return $this->migrate_template_ddl_syntax;
    }

    /**
     * @param string $migrate_template_ddl_syntax
     */
    public function setMigrateTemplateDdlSyntax(string $migrate_template_ddl_syntax): void
    {
        $this->migrate_template_ddl_syntax = $migrate_template_ddl_syntax;
    }

    /**
     * @return string
     */
    public function getMigrateGenerateTemplate(): string
    {
        return $this->migrate_generate_template;
    }

    /**
     * @param string $migrate_generate_template
     */
    public function setMigrateGenerateTemplate(string $migrate_generate_template): void
    {
        $this->migrate_generate_template = $migrate_generate_template;
    }
}
