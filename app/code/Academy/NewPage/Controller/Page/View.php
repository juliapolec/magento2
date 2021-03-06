<?php
namespace Academy\NewPage\Controller\Page;

class View extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @param \Magento\Framework\App\Action\Context            $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct
    (
        \Magento\Framework\App\Action\Context               $context,
        \Magento\Framework\Controller\Result\JsonFactory    $resultJsonFactory
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
        $this->_eventManager->dispatch('academy_eventExample_display_text', [8, 6]);

        return $result->setData($data);
    }

}
