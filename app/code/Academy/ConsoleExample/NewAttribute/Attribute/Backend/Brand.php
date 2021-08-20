<?php

namespace Academy\ConsoleExample\NewAttribute\Attribute\Backend;

class Brand extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Validate
     * @param \Magento\Catalog\Model\Product $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function validate($object)
    {
        $object->getData($this->getAttribute()->getAttributeCode());

        return true;
    }
}



