<?php

    declare(strict_types=1);
    namespace Academy\CronExample\Cron;

use Psr\Log\LoggerInterface;

    class Example
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute()
    {
        $this->logger->info('cron example works');

    }
}
