<?php

class TestPsCrossselling extends Ps_Crossselling
{
    public function exposeGetOrderProducts(array $ids)
    {
        return $this->getOrderProducts($ids);
    }

    public function exposeClearCache($template, $cacheId = null, $compileId = null)
    {
        return $this->_clearCache($template, $cacheId, $compileId);
    }
}
