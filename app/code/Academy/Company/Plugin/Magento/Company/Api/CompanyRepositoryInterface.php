<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Academy\Company\Plugin\Magento\Company\Api;

class CompanyRepositoryInterface
{

    
    /**
     * @var \Magento\Company\Api\Data\CompanyExtensionFactory
     */
    protected $companyExtensionFactory;
    
    /**
     * @var \Magento\Company\Model\CompanyRepository
     */
    protected $companyRepository;

    /**
     * @param \Magento\Company\Model\CompanyRepository $companyRepository
     * @param \Magento\Company\Api\Data\CompanyExtensionFactory $companyExtensionFactory
     */
    public function __construct(
        \Magento\Company\Model\CompanyRepository $companyRepository,
        \Magento\Company\Api\Data\CompanyExtensionFactory $companyExtensionFactory
    ) {
        $this->companyRepository = $companyRepository;
        $this->companyExtensionFactory = $companyExtensionFactory;
    }

    /**
     * @param \Magento\Company\Api\CompanyRepositoryInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterGet(
        \Magento\Company\Api\CompanyRepositoryInterface $subject,
        $result
    ) {
        $company = $result;
        $extensionAttributes = $company->getExtensionAttributes();
        $companyExtension = $extensionAttributes ? $extensionAttributes : $this->companyExtensionFactory->create();
        
        $companyExtension->setErpCustomerNumber($company->getData('erp_customer_number'));

        $company->setExtensionAttributes($companyExtension);
        return $company;
    }

    /**
     * @param \Magento\Company\Api\CompanyRepositoryInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterSave(
        \Magento\Company\Api\CompanyRepositoryInterface $subject,
        $result
    ) {
        $company = $result;
        $extensionAttributes = $company->getExtensionAttributes();
        if (!$extensionAttributes) {
            return $company;
        }
        
        $company->setData('erp_customer_number', $extensionAttributes->getErpCustomerNumber());

        $company->save();
        return $company;
    }

    /**
     * @param \Magento\Company\Api\CompanyRepositoryInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterGetList(
        \Magento\Company\Api\CompanyRepositoryInterface $subject,
        $result
    ) {
        foreach ($result->getItems() as $company) {
            $this->afterGet($subject, $company);
        }
        return $result;
    }
}

