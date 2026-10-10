<?php

namespace PrestaShop\PrestaShop\Core\Module {

interface WidgetInterface
{
    public function renderWidget($hookName, array $configuration);

    public function getWidgetVariables($hookName, array $configuration);
}

}

namespace PrestaShop\PrestaShop\Adapter\Image {

class ImageRetriever
{
    public $link;

    public function __construct($link)
    {
        $this->link = $link;
        \ImageRetrieverStub::recordConstruct($link);
    }
}

}

namespace PrestaShop\PrestaShop\Adapter\Product {

class PriceFormatter
{
    public function __construct()
    {
        \PresenterStub::recordPriceFormatter();
    }
}

class ProductColorsRetriever
{
    public function __construct()
    {
        \PresenterStub::recordProductColorsRetriever();
    }
}

}

namespace PrestaShop\PrestaShop\Adapter\Presenter\Product {

class ProductListingPresenter
{
    public $args;

    public function __construct($imageRetriever, $link, $priceFormatter, $colorsRetriever, $translator)
    {
        $this->args = array($imageRetriever, $link, $priceFormatter, $colorsRetriever, $translator);
        \PresenterStub::recordAdapterPresenterConstruct($this->args);
    }

    public function present($settings, $product, $language)
    {
        \PresenterStub::recordPresent('bulk', $settings, $product, $language);

        return array(
            'id_product' => isset($product['id_product']) ? $product['id_product'] : 0,
            'marker' => isset($product['marker']) ? $product['marker'] : 'bulk',
        );
    }
}

}

namespace PrestaShop\PrestaShop\Core\Product {

class ProductListingPresenter
{
    public $args;

    public function __construct($imageRetriever, $link, $priceFormatter, $colorsRetriever, $translator)
    {
        $this->args = array($imageRetriever, $link, $priceFormatter, $colorsRetriever, $translator);
        \PresenterStub::recordCorePresenterConstruct($this->args);
    }

    public function present($settings, $product, $language)
    {
        \PresenterStub::recordPresent('single', $settings, $product, $language);

        return array(
            'id_product' => isset($product['id_product']) ? $product['id_product'] : 0,
            'marker' => isset($product['marker']) ? $product['marker'] : 'single',
        );
    }
}

}
