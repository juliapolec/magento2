<?php

namespace Academy\Js\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class ReturnJson extends Action
{

    private $resultJsonFactory;

    public function __construct(JsonFactory $resultJsonFactory, Context $context)
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['json_data' => 'come from json']);
    }
}
