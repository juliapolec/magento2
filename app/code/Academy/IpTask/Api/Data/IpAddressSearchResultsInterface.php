<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Academy\IpTask\Api\Data;

interface IpAddressSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get IpAddress list.
     * @return \Academy\IpTask\Api\Data\IpAddressInterface[]
     */
    public function getItems();

    /**
     * Set ip list.
     * @param \Academy\IpTask\Api\Data\IpAddressInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

