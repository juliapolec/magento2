<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Academy\IpTask\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface IpAddressRepositoryInterface
{

    /**
     * Save IpAddress
     * @param \Academy\IpTask\Api\Data\IpAddressInterface $ipAddress
     * @return \Academy\IpTask\Api\Data\IpAddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Academy\IpTask\Api\Data\IpAddressInterface $ipAddress
    );

    /**
     * Retrieve IpAddress
     * @param string $ipaddressId
     * @return \Academy\IpTask\Api\Data\IpAddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($ipaddressId);

    /**
     * Retrieve IpAddress matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Academy\IpTask\Api\Data\IpAddressSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete IpAddress
     * @param \Academy\IpTask\Api\Data\IpAddressInterface $ipAddress
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Academy\IpTask\Api\Data\IpAddressInterface $ipAddress
    );

    /**
     * Delete IpAddress by ID
     * @param string $ipaddressId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($ipaddressId);
}

