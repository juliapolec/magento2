<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Academy\IpTask\Api\Data;

interface IpAddressInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const IP = 'ip';
    const CURRENT_IP_ADDDRESS = 'current_ip_adddress';
    const IPADDRESS_ID = 'ipaddress_id';

    /**
     * Get ipaddress_id
     * @return string|null
     */
    public function getIpaddressId();

    /**
     * Set ipaddress_id
     * @param string $ipaddressId
     * @return \Academy\IpTask\Api\Data\IpAddressInterface
     */
    public function setIpaddressId($ipaddressId);

    /**
     * Get ip
     * @return string|null
     */
    public function getIp();

    /**
     * Set ip
     * @param string $ip
     * @return \Academy\IpTask\Api\Data\IpAddressInterface
     */
    public function setIp($ip);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Academy\IpTask\Api\Data\IpAddressExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Academy\IpTask\Api\Data\IpAddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Academy\IpTask\Api\Data\IpAddressExtensionInterface $extensionAttributes
    );

    /**
     * Get current_ip_adddress
     * @return string|null
     */
    public function getCurrentIpAdddress();

    /**
     * Set current_ip_adddress
     * @param string $currentIpAdddress
     * @return \Academy\IpTask\Api\Data\IpAddressInterface
     */
    public function setCurrentIpAdddress($currentIpAdddress);
}

