<?php

namespace Academy\TurboModule\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class WebapiPlugin
{
    public function beforeSave(
        ProductRepositoryInterface $subject,
        ProductInterface           $product,
        $saveOptions = false
    ): void
    {

        $product->setPrice(222);

    }
}
