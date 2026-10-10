<?php

require_once __DIR__ . '/PsCrosssellingTestCase.php';

class PsCrosssellingCacheAndHookTest extends PsCrosssellingTestCase
{
    public function testCacheIdKeyPrefixesProductIds()
    {
        $this->assertSame('cid:ps_crossselling|10|20', $this->module->getCacheIdKey(array(10, 20)));
    }

    public function testClearCacheAlwaysTargetsModuleTemplate()
    {
        $this->module->exposeClearCache('other.tpl', 'cache-9');
        $last = end(ModuleStub::$clearCacheLog);
        $this->assertSame('module:ps_crossselling/views/templates/hook/ps_crossselling.tpl', $last[0]);
        $this->assertSame('cache-9', $last[1]);
    }

    public function testOrderStatusUpdateClearsCachePerProductAndCastsOrderId()
    {
        OrderDetailStub::$lines = array(
            array('product_id' => 4),
            array('product_id' => 5),
        );
        $this->module->hookActionOrderStatusPostUpdate(array('id_order' => '12'));
        $this->assertSame(12, OrderDetailStub::$lastOrderId);
        $this->assertCount(2, ModuleStub::$clearCacheLog);
        $this->assertSame('cid:ps_crossselling|4', ModuleStub::$clearCacheLog[0][1]);
        $this->assertSame('cid:ps_crossselling|5', ModuleStub::$clearCacheLog[1][1]);
    }

    public function testOrderStatusUpdateWithNoLinesClearsNothing()
    {
        OrderDetailStub::$lines = array();
        $this->module->hookActionOrderStatusPostUpdate(array('id_order' => 1));
        $this->assertSame(0, count(ModuleStub::$clearCacheLog));
    }
}
