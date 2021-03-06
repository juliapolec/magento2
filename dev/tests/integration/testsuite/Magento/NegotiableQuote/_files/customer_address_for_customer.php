<?php
/**
 * Customer address fixture with entity_id = 2
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Customer\Api\CustomerRepositoryInterface;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Customer\Model\Address $customerAddress */
$customerAddress = $objectManager->create(\Magento\Customer\Model\Address::class);
/** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
$customerRegistry = $objectManager->get(\Magento\Customer\Model\CustomerRegistry::class);
/** @var $customerRepository CustomerRepositoryInterface */
$customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
$customer = $customerRepository->get('customercompany22@example.com');
$customerAddress->isObjectNew(true);
$customerAddress->setData(
    [
        'entity_id' => 2,
        'attribute_set_id' => 2,
        'telephone' => 3468676,
        'postcode' => 75477,
        'country_id' => 'US',
        'city' => 'CityM',
        'company' => 'CompanyName',
        'street' => 'Green str, 67',
        'lastname' => 'Smith',
        'firstname' => 'John',
        'parent_id' => $customer->getId(),
        'region_id' => 1,
    ]
);
$customerAddress->save();

/** @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository */
$addressRepository = $objectManager->get(\Magento\Customer\Api\AddressRepositoryInterface::class);
$customerAddress = $addressRepository->getById(2);
$customerAddress->setCustomerId($customer->getId());
$customerAddress = $addressRepository->save($customerAddress);
$customerRegistry->remove($customerAddress->getCustomerId());
/** @var \Magento\Customer\Model\AddressRegistry $addressRegistry */
$addressRegistry = $objectManager->get(\Magento\Customer\Model\AddressRegistry::class);
$addressRegistry->remove($customerAddress->getId());
