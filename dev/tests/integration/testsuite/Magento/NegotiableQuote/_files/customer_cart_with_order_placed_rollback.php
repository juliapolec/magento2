<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
/** @var OrderInterfaceFactory $orderFactory */
$orderFactory = $objectManager->get(OrderInterfaceFactory::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$order = $orderFactory->create()->loadByIncrementId('33333');
if ($order->getId()) {
    $orderRepository->delete($order);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

Resolver::getInstance()->requireDataFixture('Magento/NegotiableQuote/_files/cart_with_item_for_customer_rollback.php');
