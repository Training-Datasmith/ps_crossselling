<?php

require_once __DIR__ . '/PsCrosssellingTestCase.php';

class PsCrosssellingConstructorTest extends PsCrosssellingTestCase
{
    public function testModuleIdentityAndTranslatedStrings()
    {
        $this->assertSame('ps_crossselling', $this->module->name);
        $this->assertSame('pricing_promotion', $this->module->tab);
        $this->assertSame('PrestaShop', $this->module->author);
        $this->assertSame('2.0.3', $this->module->version);
        $this->assertSame(0, $this->module->need_instance);
        $this->assertTrue($this->module->bootstrap);
        $this->assertSame('tr:Cross-selling:Modules.Crossselling.Admin', $this->module->displayName);
        $this->assertContains('Modules.Crossselling.Admin', $this->module->description);
    }

    public function testCompliancyRangeUsesRuntimeVersion()
    {
        $this->assertSame('1.7.2.0', $this->module->ps_versions_compliancy['min']);
        $this->assertSame(_PS_VERSION_, $this->module->ps_versions_compliancy['max']);
    }

    public function testConfigXmlVersionMatchesModuleVersion()
    {
        $xml = simplexml_load_file(dirname(__DIR__) . '/config.xml');
        $this->assertSame('2.0.3', (string) $xml->version);
    }

    public function testDirectIncludeWithoutPsVersionExitsBeforeClassLoad()
    {
        $cmd = 'php -r ' . escapeshellarg(
            'include ' . var_export(dirname(__DIR__) . '/ps_crossselling.php', true) . '; echo "LOADED";'
        );
        $output = shell_exec($cmd);
        $this->assertNotContains('LOADED', (string) $output);
    }
}
