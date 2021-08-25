<?php

namespace Academy\EventExample\Observer\Product;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class CustomPrice implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $item = $observer->getEvent()->getData('quote_item');
        $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
        $price = 9999; //custom price
        $item->setCustomPrice($price);
        $item->setOriginalCustomPrice($price);
        $item->getProduct()->setIsSuperMode(true);
    }

}
