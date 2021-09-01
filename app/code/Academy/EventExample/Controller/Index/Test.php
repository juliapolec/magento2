<?php

namespace Academy\EventExample\Controller\Index;

//class Test extends \Magento\Framework\App\Action\Action
//{
//
//    public function execute()
//    {
//        $textDisplay = new \Magento\Framework\DataObject(array('text' => 'Academy'));
//        $this->_eventManager->dispatch('academy_eventExample_display_text', ['mp_text' => $textDisplay]);
//        echo $textDisplay->getText();
//        exit;
//    }
//}

class View extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct
    (
        \Magento\Framework\App\Action\Context            $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )


    {
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $data = ['message' => 'Hello world!'];
        $this->_eventManager->dispatch('academy_eventExample_display_text', ['mp_text' => $data]);
        echo $data->getText();


    }

}
