<?php

abstract class PsCrosssellingTestCase extends PHPUnit_Framework_TestCase
{
    /** @var TestPsCrossselling */
    protected $module;

    public static function setUpBeforeClass()
    {
        if (!class_exists('Ps_Crossselling', false)) {
            require_once dirname(__DIR__) . '/ps_crossselling.php';
            require_once __DIR__ . '/Support/TestPsCrossselling.php';
        }
    }

    protected function setUp()
    {
        ConfigurationStub::reset();
        ConfigurationStub::$failUpdateKey = null;
        ToolsStub::reset();
        ModuleStub::reset();
        ModuleStub::$registerHookFailOn = null;
        EventLog::reset();
        DbStub::reset();
        DbStub::truncateAll();
        OrderDetailStub::reset();
        GroupStub::reset();
        FrontControllerStub::reset();
        PresenterStub::reset();
        ProductAssemblerStub::reset();
        SmartyStub::reset();
        HelperForm::$lastForms = array();

        $this->module = new TestPsCrossselling();
        $this->module->context = new Context();
        $this->module->smarty = new SmartyStub();
    }

    protected function tearDown()
    {
        DbStub::$pdo = null;
    }

    protected function newModule()
    {
        $module = new TestPsCrossselling();
        $module->context = new Context();
        $module->smarty = new SmartyStub();

        return $module;
    }
}
