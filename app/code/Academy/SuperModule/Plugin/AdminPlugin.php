<?php

namespace Academy\SuperModule\Plugin;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Catalog\Controller\Adminhtml\Product\Save;



class AdminPlugin
{
    private $scopeConfig;
    private $transportBuilder;



    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder

       )
    {

        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;

    }


    public function AfterExecute
    (Save $subject): void
    {

        if  ($this->scopeConfig->isSetFlag(
            'superModule/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {

            $value = $this->scopeConfig->getValue(
                'superModule/general/display_text',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $email = explode(", ", $value);

            $from = array(
                'email' => "test@webkul.com",
                'name' => 'Admin'
            );

            $product = $subject->getRequest()->getPostValue()['product'];


                $templateVars = array(

                    $product['name'],
                    $product['price']

                );

                $transport = $this->transportBuilder
                    ->setTemplateIdentifier('email_test')
                    ->setTemplateOptions
                    ([
                        'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    ])
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($email)
                    ->getTransport();

            $transport->sendMessage();

        }
    }
}
