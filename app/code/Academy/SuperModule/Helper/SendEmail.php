<?php

namespace Academy\SuperModule\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;


class SendEmail
{


    private TransportBuilder $transportBuilder;
    private ScopeConfigInterface $scopeConfig;
    private ProductRepositoryInterface $productRepository;

    public function __construct(ScopeConfigInterface $scopeConfig, TransportBuilder $transportBuilder, ProductRepositoryInterface $productRepository)
    {
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
    }

    public function Email($name, $price)
    {


            $emailTemplateIdentifier = 'email_test';
            $templateVars = [
                "product_name" => $name,
                "product_price" => $price,
            ];



        $templateOptions = array(
            'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );

        $from = array(
            'email' => "test@webkul.com",
            'name' => 'admin'
        );

        $value = $this->scopeConfig->getValue(
            'superModule/general/display_text',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );


        $email = explode(", ", $value);

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($emailTemplateIdentifier)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom($from)
            ->addTo($email)
            ->getTransport();

        $transport->sendMessage();
    }
}
