<?php

namespace Academy\EventExample\Observer;

//class DisplayText implements \Magento\Framework\Event\ObserverInterface
//{
//    public function execute(\Magento\Framework\Event\Observer $observer)
//    {
//        $data = $observer->getData();
//
//
//    }


use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Exception;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Class MyObserver
 */
class DisplayText implements ObserverInterface
{
    /**
     * @var PsrLoggerInterface
     */
    private $logger;

    /**
     * MyObserver constructor.
     *
     * @param PsrLoggerInterface $logger
     */
    public function __construct(
        PsrLoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            // some code goes here
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
