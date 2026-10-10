<?php

require_once __DIR__ . '/PsCrosssellingTestCase.php';

class PsCrosssellingGetContentTest extends PsCrosssellingTestCase
{
    public function testGetContentWithoutSubmitLeavesConfigUntouched()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        Configuration::updateValue('CROSSSELLING_DISPLAY_PRICE', 1);
        $html = $this->module->getContent();
        $this->assertInternalType('string', $html);
        $this->assertNotSame('', $html);
        $this->assertContains('FORM:submitCross', $html);
        $this->assertNotContains('OK:', $html);
        $this->assertSame(8, Configuration::get('CROSSSELLING_NBR'));
    }

    public function testSavesZeroAndOneDisplayPriceAndPositiveCount()
    {
        ToolsStub::$submitted['submitCross'] = true;
        ToolsStub::$values['CROSSSELLING_DISPLAY_PRICE'] = '0';
        ToolsStub::$values['CROSSSELLING_NBR'] = '4';
        $html = $this->module->getContent();
        $this->assertSame(0, Configuration::get('CROSSSELLING_DISPLAY_PRICE'));
        $this->assertSame(4, Configuration::get('CROSSSELLING_NBR'));
        $this->assertContains('OK:', $html);

        ToolsStub::$values['CROSSSELLING_DISPLAY_PRICE'] = '1';
        ToolsStub::$values['CROSSSELLING_NBR'] = '8';
        $this->module->getContent();
        $this->assertSame(1, Configuration::get('CROSSSELLING_DISPLAY_PRICE'));
        $this->assertSame(8, Configuration::get('CROSSSELLING_NBR'));
    }

    public function testRejectsDisplayPriceOutsideZeroAndOne()
    {
        ToolsStub::$submitted['submitCross'] = true;
        ToolsStub::$values['CROSSSELLING_DISPLAY_PRICE'] = '5';
        ToolsStub::$values['CROSSSELLING_NBR'] = '8';
        $before = count(ConfigurationStub::$updateLog);
        $html = $this->module->getContent();
        $this->assertSame($before, count(ConfigurationStub::$updateLog));
        $this->assertContains('ERR:', $html);
        $this->assertNotContains('OK:', $html);
    }

    public function testRejectsEmptyProductCount()
    {
        ToolsStub::$submitted['submitCross'] = true;
        ToolsStub::$values['CROSSSELLING_DISPLAY_PRICE'] = '1';
        ToolsStub::$values['CROSSSELLING_NBR'] = '';
        $html = $this->module->getContent();
        $this->assertContains('ERR:', $html);
        $this->assertContains('Modules.Crossselling.Admin', $html);
    }

    public function testRejectsNonNumericProductCount()
    {
        ToolsStub::$submitted['submitCross'] = true;
        ToolsStub::$values['CROSSSELLING_DISPLAY_PRICE'] = '1';
        ToolsStub::$values['CROSSSELLING_NBR'] = 'abc';
        $html = $this->module->getContent();
        $this->assertContains('ERR:', $html);
        $this->assertContains('Invalid number.', $html);
    }

    public function testRejectsNegativeProductCount()
    {
        ToolsStub::$submitted['submitCross'] = true;
        ToolsStub::$values['CROSSSELLING_DISPLAY_PRICE'] = '1';
        ToolsStub::$values['CROSSSELLING_NBR'] = '-3';
        $html = $this->module->getContent();
        $this->assertContains('ERR:', $html);
        $this->assertNotContains('OK:', $html);
        $this->assertSame(0, count(array_filter(ConfigurationStub::$updateLog, function ($row) {
            return $row[0] === 'CROSSSELLING_NBR';
        })));
    }

    public function testZeroProductCountIsRejectedWithoutSaving()
    {
        ToolsStub::$submitted['submitCross'] = true;
        ToolsStub::$values['CROSSSELLING_DISPLAY_PRICE'] = '1';
        ToolsStub::$values['CROSSSELLING_NBR'] = '0';
        $html = $this->module->getContent();
        $this->assertContains('ERR:', $html);
        $this->assertNotContains('OK:', $html);
    }

    public function testSuccessfulSaveClearsCacheAndConfirms()
    {
        ToolsStub::$submitted['submitCross'] = true;
        ToolsStub::$values['CROSSSELLING_DISPLAY_PRICE'] = '1';
        ToolsStub::$values['CROSSSELLING_NBR'] = '5';
        $html = $this->module->getContent();
        $this->assertContains('OK:', $html);
        $this->assertGreaterThanOrEqual(1, count(ModuleStub::$clearCacheLog));
    }

    public function testRejectedSaveDoesNotClearCache()
    {
        ModuleStub::$clearCacheLog = array();
        ToolsStub::$submitted['submitCross'] = true;
        ToolsStub::$values['CROSSSELLING_DISPLAY_PRICE'] = '1';
        ToolsStub::$values['CROSSSELLING_NBR'] = 'abc';
        $this->module->getContent();
        $this->assertSame(0, count(ModuleStub::$clearCacheLog));
    }
}
