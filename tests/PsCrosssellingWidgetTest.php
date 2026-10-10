<?php

require_once __DIR__ . '/PsCrosssellingTestCase.php';

class PsCrosssellingWidgetTest extends PsCrosssellingTestCase
{
    private function seedBasic()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        Fixture::insertProduct(5);
        Fixture::insertProduct(6);
        Fixture::insertProduct(99);
        Fixture::insertProduct(100);
        Fixture::insertOrder(1, 1, array(5, 6));
        Fixture::insertOrder(2, 1, array(99, 100));
    }

    public function testProductHookIgnoresCart()
    {
        $this->seedBasic();
        $cart = new CartStub();
        $cart->products = array(array('id_product' => 5));
        $vars = $this->module->getWidgetVariables('displayFooterProduct', array(
            'product' => array('id_product' => 99),
            'cart' => $cart,
        ));
        $this->assertInternalType('array', $vars);
        $this->assertSame(array(100), Fixture::productIdsFromPresenterOutput($vars['products']));
    }

    public function testShoppingCartHookUsesCartProducts()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        Fixture::insertProduct(5);
        Fixture::insertProduct(7);
        Fixture::insertProduct(8);
        Fixture::insertOrder(1, 1, array(5, 7, 8));
        $cart = new CartStub();
        $cart->products = array(array('id_product' => 5), array('id_product' => 7));
        $vars = $this->module->getWidgetVariables('displayShoppingCart', array('cart' => $cart));
        $this->assertSame(array(8), Fixture::productIdsFromPresenterOutput($vars['products']));
    }

    public function testShoppingCartFooterHookUsesCartProducts()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        Fixture::insertProduct(5);
        Fixture::insertProduct(7);
        Fixture::insertProduct(8);
        Fixture::insertOrder(1, 1, array(5, 7, 8));
        $cart = new CartStub();
        $cart->products = array(array('id_product' => 5), array('id_product' => 7));
        $vars = $this->module->getWidgetVariables('displayShoppingCartFooter', array('cart' => $cart));
        $this->assertSame(array(8), Fixture::productIdsFromPresenterOutput($vars['products']));
    }

    public function testDuplicateCartIdsCollapseInCacheKey()
    {
        $this->seedBasic();
        $cart = new CartStub();
        $cart->products = array(array('id_product' => 5), array('id_product' => 5), array('id_product' => 7));
        Fixture::insertProduct(7);
        Fixture::insertProduct(8);
        Fixture::insertOrder(3, 1, array(5, 7, 8));
        $out = $this->module->renderWidget('displayShoppingCart', array('cart' => $cart));
        $this->assertContains('cid:ps_crossselling|5|7', $out);
    }

    public function testEmptyCartDoesNotQuery()
    {
        $cart = new CartStub();
        $cart->products = array();
        $vars = $this->module->getWidgetVariables('displayShoppingCart', array('cart' => $cart));
        $this->assertFalse($vars);
        $this->assertSame(0, DbStub::$executeCount);
        $this->assertSame(0, count(PresenterStub::$presentLog));
    }

    public function testRenderWidgetAssignsProductsAndReturnsFetchOutput()
    {
        $this->seedBasic();
        $out = $this->module->renderWidget('displayFooterProduct', array('product' => array('id_product' => 5)));
        $this->assertInternalType('string', $out);
        $this->assertContains('FETCH:', $out);
        $this->assertNotEmpty(SmartyStub::$assignLog);
        $this->assertSame(array(6), Fixture::productIdsFromPresenterOutput(SmartyStub::$assignLog[0]['products']));
    }

    public function testRenderWidgetCacheHitSkipsQueryAndAssign()
    {
        $this->seedBasic();
        $cacheId = $this->module->getCacheIdKey(array(5));
        $tpl = 'module:ps_crossselling/views/templates/hook/ps_crossselling.tpl';
        ModuleStub::$isCachedMap[$tpl . '|' . $cacheId] = true;
        DbStub::$executeCount = 0;
        $out = $this->module->renderWidget('displayFooterProduct', array('product' => array('id_product' => 5)));
        $this->assertContains('FETCH:', $out);
        $this->assertSame(0, DbStub::$executeCount);
        $this->assertSame(0, count(SmartyStub::$assignLog));
    }

    public function testRenderWidgetWithNoRecommendationsDoesNotFetch()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        Fixture::insertProduct(123);
        $out = $this->module->renderWidget('displayFooterProduct', array('product' => array('id_product' => 123)));
        $this->assertFalse($out);
        $this->assertSame(0, count(ModuleStub::$fetchLog));
        $this->assertSame(0, count(PresenterStub::$presentLog));
    }

    public function testDirtyProductIdIsSanitizedInCacheKeyAndQuery()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        Fixture::insertProduct(10);
        Fixture::insertProduct(11);
        Fixture::insertProduct(12);
        Fixture::insertProduct(13);
        Fixture::insertOrder(1, 1, array(10, 11));
        Fixture::insertOrder(2, 1, array(12, 13));
        $out = $this->module->renderWidget('displayFooterProduct', array(
            'product' => array('id_product' => '10 OR 12'),
        ));
        $this->assertContains('cid:ps_crossselling|10', $out);
        $this->assertNotEmpty(SmartyStub::$assignLog);
        $this->assertSame(array(11), Fixture::productIdsFromPresenterOutput(SmartyStub::$assignLog[0]['products']));
    }
}
