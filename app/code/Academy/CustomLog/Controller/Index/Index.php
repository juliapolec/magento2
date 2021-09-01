<?php

namespace Academy\CustomLog\Controller\Index;

use Academy\CustomLog\Logger\Logger;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * Index action
     *
     * @return $this
     */
    /** @var PageFactory */
    protected $resultPageFactory;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        Context     $context,
        PageFactory $resultPageFactory,
        Logger      $logger
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->logger->error('Log Error');
        $this->logger->emergency('Log Emergency');
        $this->logger->info('Log Info');
        $this->logger->debug('Log Debug');
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
