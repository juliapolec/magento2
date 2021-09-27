<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Academy\Company\Plugin\Magento\Company\Controller\Adminhtml\Index;

class Save
{

    /**
     * @param \Magento\Company\Controller\Adminhtml\Index\Save $subject
     * @param $result
     * @return mixed
     */
    public function afterSetCompanyRequestData(
        \Magento\Company\Controller\Adminhtml\Index\Save $subject,
        $result
    ) {
        $result->setData('erp_customer_number', $subject->getRequest()->getPostValue('general')['erp_customer_number']);

        return $result;
    }
}

