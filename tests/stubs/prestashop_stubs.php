<?php

class ConfigurationStub
{
    public static $values = array();
    public static $updateLog = array();
    public static $deleteLog = array();

    public static function reset()
    {
        self::$failUpdateKey = null;
        self::$values = array(
            'CROSSSELLING_DISPLAY_PRICE' => 1,
            'CROSSSELLING_NBR' => 8,
            'PS_LANG_DEFAULT' => 1,
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG' => 0,
        );
        self::$updateLog = array();
        self::$deleteLog = array();
    }

    public static function get($key)
    {
        return isset(self::$values[$key]) ? self::$values[$key] : false;
    }

    public static $failUpdateKey = null;

    public static function updateValue($key, $value)
    {
        self::$updateLog[] = array($key, $value);
        if (self::$failUpdateKey === $key) {
            return false;
        }
        self::$values[$key] = $value;

        return true;
    }

    public static function deleteByName($key)
    {
        self::$deleteLog[] = $key;
        unset(self::$values[$key]);

        return true;
    }
}

class Configuration extends ConfigurationStub
{
}

class ToolsStub
{
    public static $submitted = array();
    public static $values = array();

    public static function reset()
    {
        self::$submitted = array();
        self::$values = array();
    }

    public static function isSubmit($key)
    {
        return isset(self::$submitted[$key]) && self::$submitted[$key];
    }

    public static function getValue($key, $default = false)
    {
        if (array_key_exists($key, self::$values)) {
            return self::$values[$key];
        }

        return $default;
    }

    public static function getAdminTokenLite($controller)
    {
        return 'token-' . $controller;
    }
}

class Tools extends ToolsStub
{
}

class EventLog
{
    public static $events = array();

    public static function reset()
    {
        self::$events = array();
    }

    public static function push($name, $detail = '')
    {
        self::$events[] = array('name' => $name, 'detail' => $detail);
    }
}

class ModuleStub
{
    public static $parentInstallResult = true;
    public static $parentUninstallResult = true;
    public static $registeredHooks = array();
    public static $registerHookFailOn = null;
    public static $clearCacheLog = array();
    public static $fetchLog = array();
    public static $assignLog = array();
    public static $isCachedMap = array();
    public static $installCallOrder = array();

    public static function reset()
    {
        self::$parentInstallResult = true;
        self::$parentUninstallResult = true;
        self::$registeredHooks = array();
        self::$clearCacheLog = array();
        self::$fetchLog = array();
        self::$assignLog = array();
        self::$isCachedMap = array();
        self::$installCallOrder = array();
    }
}

class Module
{
    public $name;
    public $tab;
    public $author;
    public $version;
    public $need_instance;
    public $ps_versions_compliancy;
    public $bootstrap;
    public $displayName;
    public $description;
    public $context;
    public $smarty;
    public $table = 'module';
    public $identifier = 'id_module';

    public function __construct()
    {
    }

    protected function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        return 'tr:' . $id . ':' . $domain;
    }

    public function install()
    {
        ModuleStub::$installCallOrder[] = 'parent_install';
        EventLog::push('parent_install');

        return ModuleStub::$parentInstallResult;
    }

    public function uninstall()
    {
        return ModuleStub::$parentUninstallResult;
    }

    public function registerHook($hook)
    {
        if (ModuleStub::$registerHookFailOn === $hook) {
            return false;
        }
        ModuleStub::$registeredHooks[] = $hook;

        return true;
    }

    protected function _clearCache($template, $cacheId = null, $compileId = null)
    {
        ModuleStub::$clearCacheLog[] = array($template, $cacheId);
        EventLog::push('clear_cache', $template . '|' . (string) $cacheId);
    }

    public function getCacheId($name)
    {
        return 'cid:' . $name;
    }

    public function isCached($template, $cacheId)
    {
        $key = $template . '|' . $cacheId;

        return isset(ModuleStub::$isCachedMap[$key]) ? ModuleStub::$isCachedMap[$key] : false;
    }

    public function fetch($template, $cacheId = null)
    {
        ModuleStub::$fetchLog[] = array($template, $cacheId);

        return 'FETCH:' . $template . ':' . $cacheId;
    }

    public function displayError($message)
    {
        return 'ERR:' . $message;
    }

    public function displayConfirmation($message)
    {
        return 'OK:' . $message;
    }
}

class SmartyStub
{
    public static $assignLog = array();

    public static function reset()
    {
        self::$assignLog = array();
    }

    public static function resetLog()
    {
        self::reset();
    }

    public function assign($variables)
    {
        self::$assignLog[] = $variables;
    }
}

class LinkStub
{
    public function getAdminLink($controller, $withToken = true)
    {
        return 'ADMINLINK:' . $controller;
    }
}

class Language
{
    public $id;

    public function __construct($id)
    {
        $this->id = (int) $id;
    }
}

class AdminControllerStub
{
    public function getLanguages()
    {
        return array(array('id_lang' => 1, 'name' => 'English'));
    }
}

class ContextStub
{
    public $link;
    public $controller;
    public $language;

    public function __construct()
    {
        $this->link = new LinkStub();
        $this->controller = new AdminControllerStub();
        $this->language = (object) array('id' => 3);
    }

    public function getTranslator()
    {
        return (object) array('id' => 'translator');
    }
}

class Context
{
    public static $instance;

    public $link;
    public $controller;
    public $language;

    public function __construct()
    {
        $stub = new ContextStub();
        $this->link = $stub->link;
        $this->controller = $stub->controller;
        $this->language = $stub->language;
        self::$instance = $this;
    }

    public function getTranslator()
    {
        return (object) array('id' => 'translator');
    }
}

class HelperForm
{
    public $show_toolbar;
    public $table;
    public $default_form_language;
    public $allow_employee_form_lang;
    public $identifier;
    public $submit_action;
    public $currentIndex;
    public $token;
    public $tpl_vars;
    public static $lastForms = array();
    public static $lastHelper = null;

    public function generateForm($forms)
    {
        self::$lastForms = $forms;
        self::$lastHelper = $this;

        return 'FORM:' . $this->submit_action . ':' . $this->token;
    }
}

class DbStub
{
    public static $pdo;
    public static $executeCount = 0;
    public static $slaveFlags = array();
    public static $schemaReady = false;

    public static function reset()
    {
        self::$executeCount = 0;
        self::$slaveFlags = array();
    }

    public static function connect()
    {
        if (self::$pdo === null) {
            $dsn = getenv('PS_TEST_DB_DSN');
            $user = getenv('PS_TEST_DB_USER');
            $pass = getenv('PS_TEST_DB_PASSWORD');
            self::$pdo = new \PDO($dsn, $user, $pass);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        if (!self::$schemaReady) {
            self::createSchema();
            self::$schemaReady = true;
        }
    }

    public static function createSchema()
    {
        $sql = array(
            'DROP TABLE IF EXISTS ps_category_group',
            'DROP TABLE IF EXISTS ps_category_product',
            'DROP TABLE IF EXISTS ps_product_shop',
            'DROP TABLE IF EXISTS ps_product',
            'DROP TABLE IF EXISTS ps_order_detail',
            'DROP TABLE IF EXISTS ps_orders',
            'CREATE TABLE ps_orders (id_order INT PRIMARY KEY, valid TINYINT NOT NULL)',
            'CREATE TABLE ps_order_detail (id_order_detail INT AUTO_INCREMENT PRIMARY KEY, id_order INT NOT NULL, product_id INT NOT NULL)',
            'CREATE TABLE ps_product (id_product INT PRIMARY KEY)',
            'CREATE TABLE ps_product_shop (id_product INT NOT NULL, id_shop INT NOT NULL, id_category_default INT NOT NULL, active TINYINT NOT NULL, visibility VARCHAR(16) NOT NULL, PRIMARY KEY (id_product, id_shop))',
            'CREATE TABLE ps_category_product (id_category INT NOT NULL, id_product INT NOT NULL)',
            'CREATE TABLE ps_category_group (id_category INT NOT NULL, id_group INT NOT NULL)',
        );

        foreach ($sql as $statement) {
            self::$pdo->exec($statement);
        }
    }

    public static function truncateAll()
    {
        self::connect();
        self::$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        foreach (array('ps_category_group', 'ps_category_product', 'ps_product_shop', 'ps_product', 'ps_order_detail', 'ps_orders') as $table) {
            self::$pdo->exec('TRUNCATE TABLE ' . $table);
        }
        self::$pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        self::$executeCount = 0;
        self::$slaveFlags = array();
    }
}

class Db
{
    private $slave;

    public function __construct($slave = false)
    {
        $this->slave = $slave;
    }

    public static function getInstance($slave = false)
    {
        DbStub::$slaveFlags[] = (bool) $slave;
        DbStub::connect();

        return new self($slave);
    }

    public function executeS($sql)
    {
        ++DbStub::$executeCount;

        return DbStub::$pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }
}

class OrderDetailStub
{
    public static $lines = array();
    public static $lastOrderId;

    public static function reset()
    {
        self::$lines = array();
        self::$lastOrderId = null;
    }
}

class OrderDetail
{
    public static function getList($orderId)
    {
        OrderDetailStub::$lastOrderId = $orderId;

        return OrderDetailStub::$lines;
    }
}

class GroupStub
{
    public static $featureActive = false;
    public static $currentId = 1;

    public static function reset()
    {
        self::$featureActive = false;
        self::$currentId = 1;
    }
}

class Group
{
    public static function isFeatureActive()
    {
        return GroupStub::$featureActive;
    }

    public static function getCurrent()
    {
        return (object) array('id' => GroupStub::$currentId);
    }
}

class FrontControllerStub
{
    public static $groups = array();

    public static function reset()
    {
        self::$groups = array();
    }
}

class FrontController
{
    public static function getCurrentCustomerGroups()
    {
        return FrontControllerStub::$groups;
    }
}

class Shop
{
    public static function addSqlAssociation($type, $alias)
    {
        return ' INNER JOIN ps_product_shop product_shop ON (product_shop.id_product = ' . $alias . '.id_product AND product_shop.id_shop = 1) ';
    }
}

class PresentationSettings
{
    public $showPrices = true;
}

class ProductPresenterFactory
{
    public $context;

    public function __construct($context)
    {
        $this->context = $context;
    }

    public function getPresentationSettings()
    {
        return new PresentationSettings();
    }
}

class ProductAssemblerStub
{
    public static $bulk = true;
    public static $assembleProductsLog = array();
    public static $assembleProductLog = array();

    public static function reset()
    {
        self::$bulk = getenv('PS_TEST_ASSEMBLER') !== 'single';
        self::$assembleProductsLog = array();
        self::$assembleProductLog = array();
    }
}

if (getenv('PS_TEST_ASSEMBLER') === 'single') {
    class ProductAssembler
    {
        public $context;

        public function __construct($context)
        {
            $this->context = $context;
        }

        public function assembleProduct($rawProduct)
        {
            ProductAssemblerStub::$assembleProductLog[] = $rawProduct;

            return array('id_product' => $rawProduct['id_product'], 'marker' => 'single');
        }
    }
} else {
    class ProductAssembler
    {
        public $context;

        public function __construct($context)
        {
            $this->context = $context;
        }

        public function assembleProducts($rawProducts)
        {
            ProductAssemblerStub::$assembleProductsLog[] = $rawProducts;
            $out = array();
            foreach ($rawProducts as $raw) {
                $out[] = array('id_product' => $raw['id_product'], 'marker' => 'bulk');
            }

            return $out;
        }

        public function assembleProduct($rawProduct)
        {
            ProductAssemblerStub::$assembleProductLog[] = $rawProduct;

            return array('id_product' => $rawProduct['id_product'], 'marker' => 'single');
        }
    }
}

class PresenterStub
{
    public static $adapterConstructCount = 0;
    public static $coreConstructCount = 0;
    public static $presentLog = array();
    public static $lastImageRetrieverLink;

    public static function reset()
    {
        self::$adapterConstructCount = 0;
        self::$coreConstructCount = 0;
        self::$presentLog = array();
        self::$lastImageRetrieverLink = null;
    }

    public static function recordAdapterPresenterConstruct($args)
    {
        ++self::$adapterConstructCount;
    }

    public static function recordCorePresenterConstruct($args)
    {
        ++self::$coreConstructCount;
    }

    public static function recordPresent($marker, $settings, $product, $language)
    {
        self::$presentLog[] = array($marker, $settings, $product, $language);
    }

    public static function recordPriceFormatter()
    {
    }

    public static function recordProductColorsRetriever()
    {
    }
}

class ImageRetrieverStub
{
    public static function recordConstruct($link)
    {
        \PresenterStub::$lastImageRetrieverLink = $link;
    }
}

class CartStub
{
    public $products = array();

    public function getProducts()
    {
        return $this->products;
    }
}

class Fixture
{
    public static function insertOrder($idOrder, $valid, array $productIds)
    {
        DbStub::connect();
        $pdo = DbStub::$pdo;
        $pdo->exec('INSERT INTO ps_orders (id_order, valid) VALUES (' . (int) $idOrder . ', ' . (int) $valid . ')');
        foreach ($productIds as $pid) {
            $pdo->exec('INSERT INTO ps_order_detail (id_order, product_id) VALUES (' . (int) $idOrder . ', ' . (int) $pid . ')');
        }
    }

    public static function insertProduct($idProduct, $active = 1, $visibility = 'both', $categoryDefault = 1)
    {
        DbStub::connect();
        $pdo = DbStub::$pdo;
        $pdo->exec('INSERT INTO ps_product (id_product) VALUES (' . (int) $idProduct . ')');
        $pdo->exec(
            'INSERT INTO ps_product_shop (id_product, id_shop, id_category_default, active, visibility) VALUES ('
            . (int) $idProduct . ', 1, ' . (int) $categoryDefault . ', ' . (int) $active . ", '" . $visibility . "')"
        );
    }

    public static function linkCategoryProduct($categoryId, $productId)
    {
        DbStub::connect();
        DbStub::$pdo->exec('INSERT INTO ps_category_product (id_category, id_product) VALUES (' . (int) $categoryId . ', ' . (int) $productId . ')');
    }

    public static function linkCategoryGroup($categoryId, $groupId)
    {
        DbStub::connect();
        DbStub::$pdo->exec('INSERT INTO ps_category_group (id_category, id_group) VALUES (' . (int) $categoryId . ', ' . (int) $groupId . ')');
    }

    public static function productIdsFromPresenterOutput($products)
    {
        $ids = array();
        if (!is_array($products)) {
            return $ids;
        }
        foreach ($products as $row) {
            $ids[] = (int) $row['id_product'];
        }
        sort($ids);

        return $ids;
    }
}
