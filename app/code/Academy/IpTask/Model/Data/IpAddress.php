<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Academy\IpTask\Model\Data;

use Academy\IpTask\Api\Data\IpAddressInterface;

class IpAddress extends \Magento\Framework\Api\AbstractExtensibleObject implements IpAddressInterface
{

    /**
     * Get ipaddress_id
     * @return string|null
     */
    public function getIpaddressId()
    {
        return $this->_get(self::IPADDRESS_ID);
    }

    /**
     * Set ipaddress_id
     * @param string $ipaddressId
     * @return \Academy\IpTask\Api\Data\IpAddressInterface
     */
    public function setIpaddressId($ipaddressId)
    {
        return $this->setData(self::IPADDRESS_ID, $ipaddressId);
    }

    /**
     * Get ip
     * @return string|null
     */
    public function getIp()
    {
        return $this->_get(self::IP);
    }

    /**
     * Set ip
     * @param string $ip
     * @return \Academy\IpTask\Api\Data\IpAddressInterface
     */
    public function setIp($ip)
    {
        return $this->setData(self::IP, $ip);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Academy\IpTask\Api\Data\IpAddressExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Academy\IpTask\Api\Data\IpAddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Academy\IpTask\Api\Data\IpAddressExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get current_ip_adddress
     * @return string|null
     */
    public function getCurrentIpAdddress()
    {
        return $this->_get(self::CURRENT_IP_ADDDRESS);
    }

    /**
     * Set current_ip_adddress
     * @param string $currentIpAdddress
     * @return \Academy\IpTask\Api\Data\IpAddressInterface
     */
    public function setCurrentIpAdddress($currentIpAdddress)
    {
        return $this->setData(self::CURRENT_IP_ADDDRESS, $currentIpAdddress);
    }
}

