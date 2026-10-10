<?php

require_once __DIR__ . '/PsCrosssellingTestCase.php';

class PsCrosssellingConfigFormTest extends PsCrosssellingTestCase
{
    public function testRenderFormTargetsCrossSellingFields()
    {
        $html = $this->module->renderForm();
        $form = HelperForm::$lastForms[0]['form'];
        $this->assertSame('switch', $form['input'][0]['type']);
        $this->assertSame('CROSSSELLING_DISPLAY_PRICE', $form['input'][0]['name']);
        $this->assertSame('CROSSSELLING_NBR', $form['input'][1]['name']);
        $this->assertSame('submitCross', HelperForm::$lastHelper->submit_action);
        $this->assertSame('token-AdminModules', HelperForm::$lastHelper->token);
        $this->assertContains('configure=ps_crossselling', HelperForm::$lastHelper->currentIndex);
        $this->assertContains('tab_module=pricing_promotion', HelperForm::$lastHelper->currentIndex);
        $this->assertContains('module_name=ps_crossselling', HelperForm::$lastHelper->currentIndex);
        $this->assertSame('FORM:submitCross:token-AdminModules', $html);
        $this->assertSame($this->module->getConfigFieldsValues(), HelperForm::$lastHelper->tpl_vars['fields_value']);
    }

    public function testConfigFieldsPreferSubmittedValues()
    {
        ToolsStub::$values['CROSSSELLING_NBR'] = '3';
        ToolsStub::$values['CROSSSELLING_DISPLAY_PRICE'] = '0';
        $values = $this->module->getConfigFieldsValues();
        $this->assertSame('3', $values['CROSSSELLING_NBR']);
        $this->assertSame('0', $values['CROSSSELLING_DISPLAY_PRICE']);
    }

    public function testConfigFieldsFallBackToStoredConfiguration()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        Configuration::updateValue('CROSSSELLING_DISPLAY_PRICE', 1);
        $values = $this->module->getConfigFieldsValues();
        $this->assertSame(8, $values['CROSSSELLING_NBR']);
        $this->assertSame(1, $values['CROSSSELLING_DISPLAY_PRICE']);
    }

    public function testEmployeeFormLanguageFlagAndDefaultLanguage()
    {
        Configuration::updateValue('PS_LANG_DEFAULT', 2);
        Configuration::updateValue('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $this->module->renderForm();
        $this->assertSame(2, HelperForm::$lastHelper->default_form_language);
        $this->assertSame(0, HelperForm::$lastHelper->allow_employee_form_lang);
        $this->assertSame(3, HelperForm::$lastHelper->tpl_vars['id_language']);
        $this->assertSame(
            array(array('id_lang' => 1, 'name' => 'English')),
            HelperForm::$lastHelper->tpl_vars['languages']
        );

        Configuration::updateValue('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 5);
        $this->module->renderForm();
        $this->assertSame(5, HelperForm::$lastHelper->allow_employee_form_lang);
    }
}
