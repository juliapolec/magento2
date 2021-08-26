<?php

namespace Academy\EventExample\Observer\Product;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class CustomPrice implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $item = $observer->getEvent()->getData('quote_item');
        $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
        $price = $item->getPrice() * 2 ; //custom price
        $qty = $item->getQty()*2; //Qty
        $item->setCustomPrice($price);
        $item->setOriginalCustomPrice($price);
        $item->setQty($qty);


        $item->getProduct()->setIsSuperMode(true);
    }

}
