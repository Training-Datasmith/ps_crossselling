<?php

require_once __DIR__ . '/PsCrosssellingTestCase.php';

class PsCrosssellingOrderProductsTest extends PsCrosssellingTestCase
{
    public function testExcludesSourceProductAndInvalidOrders()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        Fixture::insertProduct(1);
        Fixture::insertProduct(2);
        Fixture::insertProduct(3);
        Fixture::insertOrder(1, 1, array(1, 2));
        Fixture::insertOrder(2, 0, array(1, 3));
        $result = $this->module->exposeGetOrderProducts(array(1));
        $this->assertInternalType('array', $result);
        $this->assertSame(array(2), Fixture::productIdsFromPresenterOutput($result));
    }

    public function testFiltersVisibilityAndActiveFlag()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 10);
        Fixture::insertProduct(1);
        Fixture::insertProduct(2, 1, 'both');
        Fixture::insertProduct(3, 1, 'catalog');
        Fixture::insertProduct(4, 1, 'search');
        Fixture::insertProduct(5, 1, 'none');
        Fixture::insertProduct(6, 0, 'both');
        Fixture::insertOrder(1, 1, array(1, 2, 3, 4, 5, 6));
        $result = $this->module->exposeGetOrderProducts(array(1));
        $this->assertSame(array(2, 3), Fixture::productIdsFromPresenterOutput($result));
    }

    public function testDistinctProductsAndRespectsDisplayLimit()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 5);
        Fixture::insertProduct(1);
        Fixture::insertProduct(2);
        for ($i = 0; $i < 5; ++$i) {
            Fixture::insertOrder(10 + $i, 1, array(1, 2));
        }
        $result = $this->module->exposeGetOrderProducts(array(1));
        $this->assertCount(1, $result);

        DbStub::truncateAll();
        Configuration::updateValue('CROSSSELLING_NBR', 2);
        Fixture::insertProduct(1);
        foreach (array(20, 21, 22, 23) as $pid) {
            Fixture::insertProduct($pid);
        }
        Fixture::insertOrder(1, 1, array(1, 20, 21, 22, 23));
        $limited = $this->module->exposeGetOrderProducts(array(1));
        $this->assertCount(2, $limited);
    }

    public function testOrdersOlderThanLimitFactorAreExcluded()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 2);
        Fixture::insertProduct(1);
        Fixture::insertProduct(100);
        Fixture::insertProduct(200);
        for ($id = 1; $id <= 101; ++$id) {
            if ($id === 1) {
                Fixture::insertOrder($id, 1, array(1, 100));
            } elseif ($id === 101) {
                Fixture::insertOrder($id, 1, array(1, 200));
            } else {
                Fixture::insertOrder($id, 1, array(1));
            }
        }
        $result = $this->module->exposeGetOrderProducts(array(1));
        $this->assertSame(50, Ps_Crossselling::LIMIT_FACTOR);
        $this->assertSame(array(200), Fixture::productIdsFromPresenterOutput($result));
    }

    public function testOrderAtTheLimitFactorBoundaryIsIncluded()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 2);
        Fixture::insertProduct(1);
        Fixture::insertProduct(100);
        Fixture::insertProduct(200);
        for ($id = 1; $id <= 101; ++$id) {
            if ($id === 2) {
                Fixture::insertOrder($id, 1, array(1, 100));
            } elseif ($id === 101) {
                Fixture::insertOrder($id, 1, array(1, 200));
            } else {
                Fixture::insertOrder($id, 1, array(1));
            }
        }
        $result = $this->module->exposeGetOrderProducts(array(1));
        $this->assertSame(array(100, 200), Fixture::productIdsFromPresenterOutput($result));
    }

    public function testCartRecommendationsExcludeEveryCartProduct()
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

    public function testGroupFilterKeepsOnlyCurrentGroups()
    {
        GroupStub::$featureActive = true;
        FrontControllerStub::$groups = array(3, 4);
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        Fixture::insertProduct(1);
        Fixture::insertProduct(10, 1, 'both', 10);
        Fixture::insertProduct(11, 1, 'both', 11);
        Fixture::linkCategoryProduct(10, 10);
        Fixture::linkCategoryProduct(11, 11);
        Fixture::linkCategoryGroup(10, 3);
        Fixture::linkCategoryGroup(11, 9);
        Fixture::insertOrder(1, 1, array(1, 10, 11));
        $result = $this->module->exposeGetOrderProducts(array(1));
        $this->assertSame(array(10), Fixture::productIdsFromPresenterOutput($result));
    }

    public function testEmptyGroupListFallsBackToCurrentGroup()
    {
        GroupStub::$featureActive = true;
        FrontControllerStub::$groups = array();
        GroupStub::$currentId = 7;
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        Fixture::insertProduct(1);
        Fixture::insertProduct(10, 1, 'both', 10);
        Fixture::insertProduct(11, 1, 'both', 11);
        Fixture::linkCategoryProduct(10, 10);
        Fixture::linkCategoryProduct(11, 11);
        Fixture::linkCategoryGroup(10, 7);
        Fixture::linkCategoryGroup(11, 9);
        Fixture::insertOrder(1, 1, array(1, 10, 11));
        $result = $this->module->exposeGetOrderProducts(array(1));
        $this->assertSame(array(10), Fixture::productIdsFromPresenterOutput($result));
    }

    public function testShowPricesFollowsConfiguration()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        Fixture::insertProduct(1);
        Fixture::insertProduct(2);
        Fixture::insertOrder(1, 1, array(1, 2));
        Configuration::updateValue('CROSSSELLING_DISPLAY_PRICE', 1);
        $this->module->exposeGetOrderProducts(array(1));
        $this->assertTrue(PresenterStub::$presentLog[0][1]->showPrices);
        PresenterStub::reset();
        ProductAssemblerStub::reset();
        Configuration::updateValue('CROSSSELLING_DISPLAY_PRICE', 0);
        $this->module->exposeGetOrderProducts(array(1));
        $this->assertFalse(PresenterStub::$presentLog[0][1]->showPrices);
    }

    public function testNoMatchingOrdersDoesNotPresent()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        Fixture::insertProduct(123);
        $result = $this->module->exposeGetOrderProducts(array(123));
        $this->assertFalse($result);
        $this->assertSame(0, count(PresenterStub::$presentLog));
    }

    public function testGetOrderProductsCastsIdsBeforeQuery()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        Fixture::insertProduct(10);
        Fixture::insertProduct(11);
        Fixture::insertProduct(12);
        Fixture::insertProduct(13);
        Fixture::insertOrder(1, 1, array(10, 11));
        Fixture::insertOrder(2, 1, array(12, 13));
        $result = $this->module->exposeGetOrderProducts(array('10 OR 12'));
        $this->assertInternalType('array', $result);
        $this->assertSame(array(11), Fixture::productIdsFromPresenterOutput($result));
    }

    public function testDbQueriesUseSlaveFlagFalse()
    {
        Configuration::updateValue('CROSSSELLING_NBR', 8);
        Fixture::insertProduct(1);
        Fixture::insertProduct(2);
        Fixture::insertOrder(1, 1, array(1, 2));
        $this->module->exposeGetOrderProducts(array(1));
        $this->assertNotEmpty(DbStub::$slaveFlags);
        foreach (DbStub::$slaveFlags as $flag) {
            $this->assertFalse($flag);
        }
    }
}
