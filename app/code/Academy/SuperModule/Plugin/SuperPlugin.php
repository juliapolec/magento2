<?php

namespace Academy\SuperModule\Plugin;


use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;



class SuperPlugin
{
    private $scopeConfig;
    private $transportBuilder;


    public function __construct(ScopeConfigInterface $scopeConfig, TransportBuilder $transportBuilder)
    {
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;

    }


    public function beforeSave(
        ProductRepositoryInterface $subject,
        ProductInterface           $product,
                                   $saveOptions = false

    ): void
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

            try {
                $existingProduct = $subject->get($product->getSku());

                $templateVars = array(
                    "product_name" => $existingProduct->getName(),
                    "product_price" => $existingProduct->getPrice(),
                    "product_new_price" => $product->getPrice()
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


            } catch (NoSuchEntityException $e){

                $templateVarsNew = array(
                    "sku" => $product->getSku(),
                    "product_name" => $product->getName(),
                    "product_price" => $product->getPrice(),
                    "product_new_price" => $product->getPrice()
                );

                $transport = $this->transportBuilder
                    ->setTemplateIdentifier('new_email')
                    ->setTemplateOptions
                    ([
                        'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    ])
                    ->setTemplateVars($templateVarsNew)
                    ->setFrom($from)
                    ->addTo($email)
                    ->getTransport();
            }

            $transport->sendMessage();

        }
    }
}
