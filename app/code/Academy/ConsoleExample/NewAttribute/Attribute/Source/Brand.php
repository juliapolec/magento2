<?php

namespace Academy\ConsoleExample\NewAttribute\Attribute\Source;

class Brand extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('Lee'), 'value' => 'lee'],
                ['label' => __('Wrangler'), 'value' => 'wrangler'],
                ['label' => __('The north face'), 'value' => 'the north face'],
                ['label' => __('Patagonia'), 'value' => 'patagonia'],
                ['label' => __('La sportiva'), 'value' => 'la sportiva'],
                ['label' => __('Scarpa'), 'value' => 'scarpa'],
            ];
        }
        return $this->_options;
    }
}


