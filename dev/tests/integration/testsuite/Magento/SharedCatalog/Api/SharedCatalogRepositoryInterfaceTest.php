<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Api;

use Magento\AdminGws\Model\Role as AdminGwsRole;
use Magento\Authorization\Model\Role;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SharedCatalogRepositoryInterfaceTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SharedCatalogRepositoryInterface
     */
    private $repository;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->repository = $this->objectManager->create(SharedCatalogRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/SharedCatalog/_files/catalogs_for_search.php
     */
    public function testGetList()
    {
        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = $this->objectManager->create(FilterBuilder::class);

        $filter1 = $filterBuilder->setField(SharedCatalogInterface::NAME)
            ->setValue('catalog 2')
            ->create();
        $filter2 = $filterBuilder->setField(SharedCatalogInterface::NAME)
            ->setValue('catalog 3')
            ->create();
        $filter3 = $filterBuilder->setField(SharedCatalogInterface::NAME)
            ->setValue('catalog 4')
            ->create();
        $filter4 = $filterBuilder->setField(SharedCatalogInterface::NAME)
            ->setValue('catalog 5')
            ->create();
        $filter5 = $filterBuilder->setField(SharedCatalogInterface::CUSTOMER_GROUP_ID)
            ->setValue(1)
            ->create();

        /**@var SortOrderBuilder $sortOrderBuilder */
        $sortOrderBuilder = $this->objectManager->create(SortOrderBuilder::class);

        /** @var SortOrder $sortOrder */
        $sortOrder = $sortOrderBuilder->setField(SharedCatalogInterface::DESCRIPTION)
            ->setDirection(SortOrder::SORT_DESC)
            ->create();

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);

        $searchCriteriaBuilder->addFilters([$filter1, $filter2, $filter3, $filter4]);
        $searchCriteriaBuilder->addFilters([$filter5]);
        $searchCriteriaBuilder->setSortOrders([$sortOrder]);

        $searchCriteriaBuilder->setPageSize(2);
        $searchCriteriaBuilder->setCurrentPage(2);

        $searchCriteria = $searchCriteriaBuilder->create();

        $searchResult = $this->repository->getList($searchCriteria);

        $this->assertEquals(3, $searchResult->getTotalCount());
        $items = array_values($searchResult->getItems());
        $this->assertCount(1, $items);
        $this->assertEquals('catalog 4', $items[0][SharedCatalogInterface::NAME]);
    }

    /**
     * Verify admin with restriction to specific website able to get shared catalog without store id specified.
     *
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDataFixture Magento/AdminGws/_files/role_websites_login.php
     * @magentoDataFixture Magento/SharedCatalog/_files/shared_catalog_without_store.php
     * @return void
     */
    public function testGetSharedCatalogWithUserRestrictedToSpecificWebsite(): void
    {
        $adminRole = $this->objectManager->get(Role::class);
        $adminRole->load('admingws_role', 'role_name');
        $adminGwsRole = $this->objectManager->get(AdminGwsRole::class);
        $adminGwsRole->setAdminRole($adminRole);
        $sharedCatalogCollection = $this->objectManager->get(Collection::class);
        $sharedCatalogId = $sharedCatalogCollection->getLastItem()->getId();
        $sharedCatalog = $this->repository->get($sharedCatalogId);
        self::assertNotEmpty($sharedCatalog->getId());
    }
}
