<?php
namespace Concrete\Package\MigrationTool;

use Concrete\Core\Asset\AssetList;
use Concrete\Core\Messenger\MessageBusManager;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Type\Type;
use Page;
use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\Manager;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\BatchValidator;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\Pipeline\Stage;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\StandardValidator;
use PortlandLabs\Concrete5\MigrationTool\Event\EventSubscriber;
use PortlandLabs\Concrete5\MigrationTool\Exporter\Item\Type\Manager as ExporterItemTypeManager;
use PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Attribute\Category\Manager as AttributeCategoryManager;
use PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Attribute\Key\Manager as AttributeKeyManager;
use PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Attribute\Value\Manager as AttributeValueManager;
use PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Block\Manager as CIFBlockManager;
use PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Express\Control\Manager as ExpressControlManager;
use PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Manager as CIFImportManager;
use PortlandLabs\Concrete5\MigrationTool\Importer\CIF\PageType\PublishTarget\Manager as PublishTargetManager;
use PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Permission\AccessEntity\Manager as AccessEntityManager;
use PortlandLabs\Concrete5\MigrationTool\Importer\ParserManager;
use PortlandLabs\Concrete5\MigrationTool\Importer\Wordpress\Block\Manager as WordpressBlockManager;
use PortlandLabs\Concrete5\MigrationTool\Importer\Wordpress\Manager as WordpressImportManager;
use PortlandLabs\Concrete5\MigrationTool\Messenger\Middleware\PublisherExceptionHandlingMiddleware;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Block\Manager as BlockPublisherManager;
use PortlandLabs\Concrete5\MigrationTool\Publisher\ContentImporter\ValueInspector\InspectionRoutine\BatchPageRoutine;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Routine\Manager as PublisherManager;
use SinglePage;

class Controller extends Package
{
    protected $pkgHandle = 'migration_tool';
    protected $appVersionRequired = '9.0.0';
    protected $pkgVersion = '9.1.0';
    protected $pkgAutoloaderMapCoreExtensions = true;
    protected $pkgAutoloaderRegistries = array(
        'src/PortlandLabs/Concrete5/MigrationTool' => '\PortlandLabs\Concrete5\MigrationTool',
    );

    protected $singlePages = array(
        '/dashboard/system/migration',
        '/dashboard/system/migration/import',
        '/dashboard/system/migration/import/settings',
        '/dashboard/system/migration/import/settings/basics',
        '/dashboard/system/migration/import/settings/files',
        '/dashboard/system/migration/export',
        '/dashboard/system/migration/logs',
    );

    protected $singlePagesToExclude = array(
        '/dashboard/system/migration/import/settings',
    );

    protected function getSinglePageTitle($path)
    {
        switch ($path) {
            case '/dashboard/system/migration':
                return t('Migration Tool');
            case '/dashboard/system/migration/import':
                return t('Import Content');
            case '/dashboard/system/migration/export':
                return t('Export Content');
            case '/dashboard/system/migration/logs':
                return t('Publish Logs');
        }
    }

    public function uninstall()
    {
        // Clear tables
        parent::uninstall();
        $db = \Database::connection();
        $db->Execute('SET foreign_key_checks = 0');
        $db->Execute('drop table MigrationContentMapperTargetItems');
        $tables = $db->GetCol("show tables like 'MigrationImport%'");
        foreach ($tables as $table) {
            $db->Execute('drop table ' . $table);
        }
        $tables = $db->GetCol("show tables like 'MigrationExport%'");
        foreach ($tables as $table) {
            $db->Execute('drop table ' . $table);
        }
        $tables = $db->GetCol("show tables like 'MigrationPublisher%'");
        foreach ($tables as $table) {
            $db->Execute('drop table ' . $table);
        }
        $db->Execute('SET foreign_key_checks = 1');
    }

    protected function installSinglePages($pkg)
    {
        foreach ($this->singlePages as $path) {
            if (Page::getByPath($path)->getCollectionID() <= 0) {
                SinglePage::add($path, $pkg);
            }

            $pp = Page::getByPath($path);
            if (in_array($path, $this->singlePagesToExclude)) {
                if (is_object($pp) && !$pp->isError()) {
                    $pp->setAttribute('exclude_nav', true);
                    $pp->setAttribute('exclude_search_index', true);
                }
            }

            $title = $this->getSinglePageTitle($path);
            if (isset($title)) {
                $pp->update(array('cName' => $title));
            }
        }
    }

    public function on_start()
    {
        \Core::bind('migration/batch/page/validator', function ($app, $batch) {
            if (isset($batch[0])) {
                $validator = new StandardValidator($batch[0]);
                $validator->addPipelineStage(new Stage\ValidateAttributesStage());
                $validator->addPipelineStage(new Stage\ValidatePageTemplatesStage());
                $validator->addPipelineStage(new Stage\ValidateAreasStage());
                $validator->addPipelineStage(new Stage\ValidateBlocksStage());
                $validator->addPipelineStage(new Stage\ValidatePageTypesStage());
                $validator->addPipelineStage(new Stage\ValidatePagePathStage());
                $validator->addPipelineStage(new Stage\ValidateExternalLinkStage());
                $validator->addPipelineStage(new Stage\ValidateAliasPage());
                $validator->addPipelineStage(new Stage\ValidateUsersStage());
                return $validator;
            }
        });

        \Core::bind('migration/batch/site/validator', function ($app, $batch) {
            if (isset($batch[0])) {
                $validator = new StandardValidator();
                $validator->addPipelineStage(new Stage\ValidateAttributesStage());
                return $validator;
            }
        });

        \Core::bind('migration/batch/user/validator', function ($app, $batch) {
            if (isset($batch[0])) {
                $validator = new StandardValidator();
                $validator->addPipelineStage(new Stage\ValidateAttributesStage());
                $validator->addPipelineStage(new Stage\ValidateUserGroupsStage());
                return $validator;
            }
        });

        \Core::bind('migration/batch/express/entry/validator', function ($app, $batch) {
            if (isset($batch[0])) {
                $validator = new StandardValidator();
                $validator->addPipelineStage(new Stage\ValidateExpressAttributesStage());
                return $validator;
            }
        });

        \Core::bindShared('migration/batch/validator', function ($app, $batch) {
            $validator = new BatchValidator();
            $validator->addPipelineStage(new Stage\ValidateBatchRecordsStage());
            return $validator;
        });

        \Core::bind('migration/batch/block/validator', function ($app, $batch) {
            $validator = new StandardValidator();
            $validator->addPipelineStage(new Stage\ValidateBlockTypesStage());
            $validator->addPipelineStage(new Stage\ValidateReferencedStacksStage());
            $validator->addPipelineStage(new Stage\ValidateReferencedContentItemsStage());
            $validator->addPipelineStage(new Stage\ValidateBlockValuesStage());
            return $validator;
        });

        \Core::bindShared('migration/manager/mapping', function ($app) {
            return new Manager($app);
        });
        \Core::bindShared('migration/manager/transforms', function ($app) {
            return new \PortlandLabs\Concrete5\MigrationTool\Batch\ContentTransformer\Manager($app);
        });
        \Core::bindShared('migration/manager/import/attribute/value', function ($app) {
            return new AttributeValueManager($app);
        });
        \Core::bindShared('migration/manager/import/attribute/key', function ($app) {
            return new AttributeKeyManager($app);
        });
        \Core::bindShared('migration/manager/import/express/control', function ($app) {
            return new ExpressControlManager($app);
        });
        \Core::bindShared('migration/manager/import/attribute/category', function ($app) {
            return new AttributeCategoryManager($app);
        });
        \Core::bindShared('migration/manager/import/permission/access_entity', function ($app) {
            return new AccessEntityManager($app);
        });

        \Core::bindShared('migration/manager/import/page_type/publish_target', function ($app) {
            return new PublishTargetManager($app);
        });

        \Core::bindShared('migration/manager/import/cif_block', function ($app) {
            return new CIFBlockManager($app);
        });
        \Core::bindShared('migration/manager/import/wordpress_block', function ($app) {
            return new WordpressBlockManager($app);
        });
        \Core::bindShared('migration/manager/importer/parser', function ($app) {
            return new ParserManager($app);
        });
        \Core::bindShared('migration/manager/importer/cif', function ($app) {
            return new CIFImportManager($app);
        });
        \Core::bindShared('migration/manager/importer/wordpress', function ($app) {
            return new WordpressImportManager($app);
        });
        \Core::bindShared('migration/manager/publisher', function ($app) {
            return new PublisherManager($app);
        });
        \Core::bindShared('migration/manager/publisher/block', function ($app) {
            return new BlockPublisherManager($app);
        });

        \Core::bind('migration/import/value_inspector', function ($app, $args) {
            $inspector = $app->make('import/value_inspector');
            $inspector->registerInspectionRoutine(new BatchPageRoutine($args[0]));
            return $inspector;
        });

        \Core::bindShared('migration/manager/exporters', function ($app) {
            return new ExporterItemTypeManager($app);
        });

        $al = AssetList::getInstance();
        $al->register('javascript', 'migration_tool/backend', 'assets/js/backend.js', [], $this);
        $al->register('css', 'migration_tool/backend', 'assets/css/backend.css', [], $this);
        $al->registerGroup('migration_tool/backend', array(
            ['javascript', 'migration_tool/backend'],
            ['css', 'migration_tool/backend'],
        ));

        $subscriber = $this->app->make(EventSubscriber::class);
        $dispatcher = $this->app->make('director');
        $dispatcher->addSubscriber($subscriber);

        $this->app->make(MessageBusManager::class)
            ->addMiddleware(PublisherExceptionHandlingMiddleware::class);
    }
    

    public function getPackageDescription()
    {
        return t("Migration Tool");
    }

    public function getPackageName()
    {
        return t("Migration Tool");
    }

    public function install()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            throw new \Exception(t('This add-on requires PHP 5.4 or greater.'));
        }
        $pkg = parent::install();
        $this->installSinglePages($pkg);
        $this->installPageTypes($pkg);
    }

    protected function installPagetypes($pkg)
    {
        $type = Type::getByHandle('import_batch');
        if (!is_object($type)) {
            Type::add(array(
                'internal' => true,
                'name' => 'Import Batch',
                'handle' => 'import_batch',
            ));
        }
    }

    public function upgrade()
    {
        parent::upgrade();
        $pkg = \Package::getByHandle('migration_tool');
        $this->installSinglePages($pkg);
        $this->installPageTypes($pkg);
    }
}
