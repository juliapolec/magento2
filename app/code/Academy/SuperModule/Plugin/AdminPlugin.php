<?php

namespace Academy\SuperModule\Plugin;

use Academy\SuperModule\Helper\SendEmail;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Save;



class AdminPlugin
{
    private ScopeConfigInterface $scopeConfig;
    private SendEmail $sendEmail;



    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SendEmail $sendEmail

       )
    {

        $this->scopeConfig = $scopeConfig;
        $this->sendEmail = $sendEmail;

    }


    public function beforeExecute
    (Save $subject): void
    {
        $product = $subject->getRequest()->getPostValue()['product'];

        if  ($this->scopeConfig->isSetFlag(
            'superModule/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {

            $this->sendEmail->Email(
                $product['name'],
                $product['price']
            );

        }
    }
}
