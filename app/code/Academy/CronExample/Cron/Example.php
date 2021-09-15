<?php

    declare(strict_types=1);
    namespace Academy\CronExample\Cron;

use Psr\Log\LoggerInterface;
use Academy\IpTask\Api\Data\IpAddressInterface;
use Academy\IpTask\Api\IpAddressRepositoryInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;


    class Example
{
        protected $logger;
        private IpAddressRepositoryInterface $ipAddressRepository;
        private IpAddressInterface $ipAddress;
        private \Magento\Store\Model\StoreManagerInterface $storeManager;
        private RemoteAddress $remote;


        public function __construct(
        LoggerInterface $logger,
        IpAddressRepositoryInterface $ipAddressRepository,
        IpAddressInterface $ipAddress,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        RemoteAddress $remote )

    {
        $this->logger = $logger;
        $this->ipAddressRepository = $ipAddressRepository;
        $this->ipAddress = $ipAddress;
        $this->storeManager = $storeManager;
        $this->remote = $remote;

    }

    public function execute()
    {
        $ipAddress = file_get_contents('https://ipinfo.io/ip');

            $this->logger->info('cron example works');

        $this->ipAddress->setCurrentIpAdddress($ipAddress);
        $this->ipAddressRepository->save($this->ipAddress);
    }
}

