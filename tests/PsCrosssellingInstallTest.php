<?php

require_once __DIR__ . '/PsCrosssellingTestCase.php';

class PsCrosssellingInstallTest extends PsCrosssellingTestCase
{
    public function testInstallWritesDefaultsAndRegistersHooks()
    {
        $this->assertTrue($this->module->install());
        $this->assertSame(1, Configuration::get('CROSSSELLING_DISPLAY_PRICE'));
        $this->assertSame(8, Configuration::get('CROSSSELLING_NBR'));
        $this->assertSame(array('displayFooterProduct', 'actionOrderStatusPostUpdate'), ModuleStub::$registeredHooks);
    }

    public function testInstallClearsModuleTemplateCacheBeforeParentInstall()
    {
        $this->module->install();
        $names = array();
        foreach (EventLog::$events as $event) {
            $names[] = $event['name'];
        }
        $clearPos = array_search('clear_cache', $names, true);
        $installPos = array_search('parent_install', $names, true);
        $this->assertNotFalse($clearPos);
        $this->assertNotFalse($installPos);
        $this->assertLessThan($installPos, $clearPos);
        $this->assertSame(
            'module:ps_crossselling/views/templates/hook/ps_crossselling.tpl|',
            EventLog::$events[$clearPos]['detail']
        );
    }

    public function testInstallStopsWhenParentInstallFails()
    {
        ModuleStub::$parentInstallResult = false;
        $this->assertFalse($this->module->install());
        $this->assertSame(array(), ConfigurationStub::$updateLog);
        $this->assertSame(array(), ModuleStub::$registeredHooks);
    }

    public function testInstallStopsWhenFirstConfigWriteFails()
    {
        ConfigurationStub::$failUpdateKey = 'CROSSSELLING_DISPLAY_PRICE';
        $this->assertFalse($this->module->install());
        $this->assertSame(array(), ModuleStub::$registeredHooks);
        $this->assertNotContains('CROSSSELLING_NBR', array_map(function ($row) {
            return $row[0];
        }, ConfigurationStub::$updateLog));
    }

    public function testInstallStopsWhenFirstHookRegistrationFails()
    {
        ModuleStub::$registerHookFailOn = 'displayFooterProduct';
        $this->assertFalse($this->module->install());
        $this->assertNotContains('actionOrderStatusPostUpdate', ModuleStub::$registeredHooks);
    }

    public function testUninstallDeletesBothKeys()
    {
        Configuration::updateValue('CROSSSELLING_DISPLAY_PRICE', 1);
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        $this->assertTrue($this->module->uninstall());
        $this->assertContains('CROSSSELLING_DISPLAY_PRICE', ConfigurationStub::$deleteLog);
        $this->assertContains('CROSSSELLING_NBR', ConfigurationStub::$deleteLog);
        $this->assertNotEmpty(ModuleStub::$clearCacheLog);
    }

    public function testUninstallStopsWhenParentUninstallFails()
    {
        ModuleStub::$parentUninstallResult = false;
        Configuration::updateValue('CROSSSELLING_DISPLAY_PRICE', 1);
        $this->assertFalse($this->module->uninstall());
        $this->assertSame(array(), ConfigurationStub::$deleteLog);
    }
}
