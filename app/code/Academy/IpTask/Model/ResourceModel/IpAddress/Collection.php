<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Academy\IpTask\Model\ResourceModel\IpAddress;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'ipaddress_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Academy\IpTask\Model\IpAddress::class,
            \Academy\IpTask\Model\ResourceModel\IpAddress::class
        );
    }
}

