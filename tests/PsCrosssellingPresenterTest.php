<?php

require_once __DIR__ . '/PsCrosssellingTestCase.php';

class PsCrosssellingPresenterTest extends PsCrosssellingTestCase
{
    public function testPresenterSelectionAndAssemblePath()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        Fixture::insertProduct(1);
        Fixture::insertProduct(2);
        Fixture::insertOrder(1, 1, array(1, 2));
        $this->module->exposeGetOrderProducts(array(1));

        if (version_compare(_PS_VERSION_, '1.7.5', '>=')) {
            $this->assertSame(1, PresenterStub::$adapterConstructCount);
            $this->assertSame(0, PresenterStub::$coreConstructCount);
            $this->assertNotEmpty(ProductAssemblerStub::$assembleProductsLog);
            $this->assertSame('bulk', PresenterStub::$presentLog[0][0]);
        } else {
            $this->assertSame(0, PresenterStub::$adapterConstructCount);
            $this->assertSame(1, PresenterStub::$coreConstructCount);
            $this->assertNotEmpty(ProductAssemblerStub::$assembleProductLog);
            $this->assertSame('single', PresenterStub::$presentLog[0][0]);
        }
    }

    public function testPresenterReceivesContextCollaboratorsAndLanguage()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        Fixture::insertProduct(1);
        Fixture::insertProduct(2);
        Fixture::insertOrder(1, 1, array(1, 2));
        $this->module->exposeGetOrderProducts(array(1));
        $this->assertSame($this->module->context->link, PresenterStub::$lastImageRetrieverLink);
        $this->assertSame($this->module->context->language, PresenterStub::$presentLog[0][3]);
    }
}
