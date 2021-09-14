<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Academy\IpTask\Model;

use Academy\IpTask\Api\Data\IpAddressInterfaceFactory;
use Academy\IpTask\Api\Data\IpAddressSearchResultsInterfaceFactory;
use Academy\IpTask\Api\IpAddressRepositoryInterface;
use Academy\IpTask\Model\ResourceModel\IpAddress as ResourceIpAddress;
use Academy\IpTask\Model\ResourceModel\IpAddress\CollectionFactory as IpAddressCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class IpAddressRepository implements IpAddressRepositoryInterface
{

    protected $resource;

    protected $ipAddressFactory;

    protected $searchResultsFactory;

    protected $extensibleDataObjectConverter;
    private $storeManager;

    protected $dataIpAddressFactory;

    protected $dataObjectHelper;

    protected $ipAddressCollectionFactory;

    protected $dataObjectProcessor;

    protected $extensionAttributesJoinProcessor;

    private $collectionProcessor;


    /**
     * @param ResourceIpAddress $resource
     * @param IpAddressFactory $ipAddressFactory
     * @param IpAddressInterfaceFactory $dataIpAddressFactory
     * @param IpAddressCollectionFactory $ipAddressCollectionFactory
     * @param IpAddressSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceIpAddress $resource,
        IpAddressFactory $ipAddressFactory,
        IpAddressInterfaceFactory $dataIpAddressFactory,
        IpAddressCollectionFactory $ipAddressCollectionFactory,
        IpAddressSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->ipAddressFactory = $ipAddressFactory;
        $this->ipAddressCollectionFactory = $ipAddressCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataIpAddressFactory = $dataIpAddressFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Academy\IpTask\Api\Data\IpAddressInterface $ipAddress
    ) {
        /* if (empty($ipAddress->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $ipAddress->setStoreId($storeId);
        } */
        
        $ipAddressData = $this->extensibleDataObjectConverter->toNestedArray(
            $ipAddress,
            [],
            \Academy\IpTask\Api\Data\IpAddressInterface::class
        );
        
        $ipAddressModel = $this->ipAddressFactory->create()->setData($ipAddressData);
        
        try {
            $this->resource->save($ipAddressModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the ipAddress: %1',
                $exception->getMessage()
            ));
        }
        return $ipAddressModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($ipAddressId)
    {
        $ipAddress = $this->ipAddressFactory->create();
        $this->resource->load($ipAddress, $ipAddressId);
        if (!$ipAddress->getId()) {
            throw new NoSuchEntityException(__('IpAddress with id "%1" does not exist.', $ipAddressId));
        }
        return $ipAddress->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->ipAddressCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Academy\IpTask\Api\Data\IpAddressInterface::class
        );
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Academy\IpTask\Api\Data\IpAddressInterface $ipAddress
    ) {
        try {
            $ipAddressModel = $this->ipAddressFactory->create();
            $this->resource->load($ipAddressModel, $ipAddress->getIpaddressId());
            $this->resource->delete($ipAddressModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the IpAddress: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ipAddressId)
    {
        return $this->delete($this->get($ipAddressId));
    }
}

