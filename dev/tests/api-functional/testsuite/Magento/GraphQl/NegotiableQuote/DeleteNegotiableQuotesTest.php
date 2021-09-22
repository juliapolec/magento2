<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\NegotiableQuote;

use Exception;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\Data\CompanyInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\NegotiableQuote\Model\HistoryManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\GraphQl\NegotiableQuote\Fixtures\CustomerRequestNegotiableQuote;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage to delete negotiable quotes.
 */
class DeleteNegotiableQuotesTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var NegotiableQuoteManagementInterface
     */
    private $negotiableQuoteManagement;

    /**
     * @var NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var HistoryManagementInterface
     */
    private $negotiableQuoteHistoryManagement;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);
        $this->negotiableQuoteManagement = $objectManager->get(NegotiableQuoteManagementInterface::class);
        $this->negotiableQuoteRepository = $objectManager->get(NegotiableQuoteRepositoryInterface::class);
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->cartRepository = $objectManager->get(CartRepositoryInterface::class);
        $this->negotiableQuoteHistoryManagement = $objectManager->get(HistoryManagementInterface::class);
    }

    /**
     *  Test delete single negotiable quote
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_closed.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testDeleteNegotiableQuotes(): void
    {
        $query = $this->getQuery('"nq_customer_closed_mask"');
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->assertNotEmpty($response['deleteNegotiableQuotes']);
        $this->assertArrayHasKey('items', $response['deleteNegotiableQuotes']['negotiable_quotes']);
        $this->assertArrayHasKey('page_info', $response['deleteNegotiableQuotes']['negotiable_quotes']);
        $this->assertArrayHasKey('total_count', $response['deleteNegotiableQuotes']['negotiable_quotes']);
        $this->assertEquals(0, $response['deleteNegotiableQuotes']['negotiable_quotes']['total_count']);
        $this->assertEmpty($response['deleteNegotiableQuotes']['negotiable_quotes']['items']);
    }

    /**
     *  Test delete multiple negotiable quotes
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/two_simple_products_for_quote.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @dataProvider dataProviderNegotiableQuotes
     *
     * @param array $negotiableQuoteData
     * @throws Exception
     */
    public function testDeleteMultipleNegotiableQuotes($negotiableQuoteData): void
    {
        try {
            /** @var CustomerRequestNegotiableQuote $requestNegotiableQuoteFixture */
            $requestNegotiableQuoteFixture = Bootstrap::getObjectManager()->create(CustomerRequestNegotiableQuote::class);
            $requestedNegotiableQuotes = $requestNegotiableQuoteFixture->requestNegotiableQuotes
            (
                ['email' => 'customercompany22@example.com', 'password' => 'password'],
                $negotiableQuoteData
            );
            //collect all the negotiable quotes created for the customer
            $negotiableQuoteIds = [];
            foreach ($requestedNegotiableQuotes as $requestedNegotiableQuote) {
                array_push($negotiableQuoteIds, $requestedNegotiableQuote['uid']);
            }
            //Close the last negotiable quote
            $lastNegotiableQuoteId = end($negotiableQuoteIds);
            $this->closeNegotiableQuoteQuery($lastNegotiableQuoteId);

            $customer = $this->customerRepository->get('customercompany22@example.com');
            $negotiableQuotes = $this->negotiableQuoteRepository->getListByCustomerId($customer->getId());
            $firstNegotiableQuoteId = array_key_first($negotiableQuotes);

            /** @var NegotiableQuoteInterface $firstNegotiableQuote */
            $firstNegotiableQuote = $this->negotiableQuoteRepository->getById($firstNegotiableQuoteId);
            $firstQuote = $this->cartRepository->get($firstNegotiableQuote->getQuoteId());
            $this->negotiableQuoteManagement->recalculateQuote($firstQuote->getId(), true);
            $firstNegotiableQuote->setStatus(NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN);
            $this->negotiableQuoteRepository->save($firstNegotiableQuote);
            $this->negotiableQuoteHistoryManagement->updateLog($firstQuote->getId());

            // Delete the first and last negotiable quotes out of the three created
            $query =  <<<MUTATION
mutation {
  deleteNegotiableQuotes(
    input: {
       quote_uids: ["{$negotiableQuoteIds[0]}", "{$negotiableQuoteIds[2]}"]
    }
  ) {
    negotiable_quotes {
      total_count
      page_info { total_pages }
      items { uid name status  comments{text creator_type author{firstname}}
       history{
      change_type
      changes {
        comment_added{ comment }
        statuses { changes{ new_status old_status }
         }
       }
     }
    }
   }
  }
}
MUTATION;
            $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
            $this->assertArrayHasKey('negotiable_quotes', $response['deleteNegotiableQuotes']);
            $this->assertEquals(1, $response['deleteNegotiableQuotes']['negotiable_quotes']['total_count']);
            $pageInfo = $response['deleteNegotiableQuotes']['negotiable_quotes']['page_info'];
            $this->assertEquals(1, $pageInfo['total_pages']);
            $this->assertNotEmpty($response['deleteNegotiableQuotes']['negotiable_quotes']['items']);
            $responseNegotiableQuoteItems = $response['deleteNegotiableQuotes']['negotiable_quotes']['items'];
            $this->assertEquals('Test Quote Name 2', $responseNegotiableQuoteItems[0]['name']);

            $leftOverNegotiableQuoteId = $responseNegotiableQuoteItems[0]['uid'];
            $deletedNegotiableQuoteIds = [$negotiableQuoteIds[0], $negotiableQuoteIds[2]];
            $this->assertTrue(in_array($leftOverNegotiableQuoteId,
                    array_diff($negotiableQuoteIds, $deletedNegotiableQuoteIds)
                )
            );
        } finally {
            //clean up the created quotes
        $this->deleteQuotes();
       }
    }

    /**
     * @return array|array[]
     */
    public function dataProviderNegotiableQuotes(): array {
        return [
            'negotiableQuotes'=> [
                'items' =>[
                    [
                        'name' => 'Test Quote Name 1',
                        'comment' => 'Test Quote comment 1',
                        'productSku' => 'simple',
                        'productQuantity' => 2
                    ],
                    [
                        'name' => 'Test Quote Name 2',
                        'comment' => 'Test Quote comment 2',
                        'productSku' => 'simple_for_quote',
                        'productQuantity' => 2
                    ],
                    [
                        'name' => 'Test Quote Name 3',
                        'comment' => 'Test Quote comment 3',
                        'productSku' => 'simple',
                        'productQuantity' => 3
                    ],
                ]
            ]
        ];
    }

    /**
     * Schema mutation to close negotiable quotes.
     *
     * @param string $quoteIds
     */
    private function closeNegotiableQuoteQuery(string $quoteIds)
    {
      $closeNegotiableQuoteQuery = <<<MUTATION
mutation {
  closeNegotiableQuotes(
    input: {
      quote_uids: ["{$quoteIds}"]
    }
  ) {
    closed_quotes {
      uid
      status
      name
      history {
       change_type
        changes {
          statuses {
            changes { new_status old_status } }
      }
    }
  }
    negotiable_quotes {
        total_count
        items { uid name status history { change_type } }
    }
  }
}
MUTATION;

        $response = $this->graphQlMutation(
            $closeNegotiableQuoteQuery,
            [],
            '',
            $this->getHeaderMap()
        );
        $this->assertArrayHasKey('closed_quotes', $response['closeNegotiableQuotes']);
    }

    /**
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @dataProvider dataProviderInvalidInfo
     *
     * @param string $customerEmail
     * @param string $customerPassword
     * @param string $message
     * @throws Exception
     */
    public function testDeleteNegotiableQuoteInvalidData(
        string $customerEmail,
        string $customerPassword,
        string $message
    ): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($message);

        $query = $this->getQuery('"nq_customer_closed_mask"');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap($customerEmail, $customerPassword));
    }

    /**
     * @return array
     */
    public function dataProviderInvalidInfo(): array
    {
        return [
            'invalid_customer_email' => [
                'customer$example.com',
                'Magento777',
                'The account sign-in was incorrect or your account is disabled temporarily. ' .
                'Please wait and try again later.'
            ],
            'invalid_customer_password' => [
                'customercompany22@example.com',
                '__--++#$@',
                'The account sign-in was incorrect or your account is disabled temporarily. ' .
                'Please wait and try again later.'
            ],
            'no_such_email' => [
                'customerNoSuch@example.com',
                'password',
                'The account sign-in was incorrect or your account is disabled temporarily. ' .
                'Please wait and try again later.'
            ]
        ];
    }

    /**
     * Testing for guest customer token
     *
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @throws Exception
     */
    public function testDeleteNegotiableQuoteWithNoCustomerToken(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current user is not a registered customer and cannot perform operations '
            . 'on negotiable quotes.');

        $query = $this->getQuery('"nq_customer_mask"');
        $this->graphQlMutation($query);
    }

    /**
     * Testing for module enabled
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 0
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @throws Exception
     */
    public function testDeleteNegotiableQuoteNoModuleEnabled(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The Negotiable Quote module is not enabled.');

        $query = $this->getQuery('"nq_customer_mask"');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Testing for customer belongs to a company
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_no_company.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @throws Exception
     */
    public function testDeleteNegotiableQuoteCustomerNoCompany(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current customer does not belong to a company.');

        $query = $this->getQuery('"nq_customer_mask"');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap('customernocompany@example.com'));
    }

    /**
     * Testing for feature enabled on company
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @throws Exception
     */
    public function testDeleteNegotiableQuoteNoCompanyFeatureEnabled(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Negotiable quotes are not enabled for the current customer\'s company.');

        /** @var CompanyInterfaceFactory $companyFactory */
        $companyFactory = Bootstrap::getObjectManager()->get(CompanyInterfaceFactory::class);
        /** @var CompanyInterface $company */
        $company = $companyFactory->create()->load('email@companyquote.com', 'company_email');
        $company->getExtensionAttributes()->getQuoteConfig()->setIsQuoteEnabled(false);
        /** @var CompanyRepositoryInterface $companyRepository */
        $companyRepository = Bootstrap::getObjectManager()->create(CompanyRepositoryInterface::class);
        $companyRepository->save($company);

        $query = $this->getQuery('"nq_customer_mask"');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Testing for manage permissions
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_view_permissions.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @throws Exception
     */
    public function testDeleteNegotiableQuoteNoManagePermissions(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current customer does not have permission to manage negotiable quotes.');

        $query = $this->getQuery('"nq_customer_mask"');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Testing for quote ownership
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @throws Exception
     */
    public function testDeleteNegotiableQuoteUnownedQuote(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not find negotiable quotes with the following UIDs: nq_admin_mask');

        $query = $this->getQuery('"nq_admin_mask"');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Testing that quote is negotiable
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/cart_empty_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testDeleteNegotiableQuoteNonNegotiable(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not find negotiable quotes with the following UIDs: '
            . 'cart_empty_customer_mask');

        $query = $this->getQuery('"cart_empty_customer_mask"');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Testing that quote is in a valid status
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testDeleteNegotiableQuoteBadStatus(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'The quotes with the following UIDs have a status that does not allow them to be deleted: '
            . 'nq_customer_mask'
        );

        $query = $this->getQuery('"nq_customer_mask"');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Testing that a quote for a different store on the same website is accessible
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_closed.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/second_store.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testDeleteNegotiableQuoteForSecondStore(): void
    {
        $this->storeManager->setCurrentStore('secondstore');
        $headers = $this->getHeaderMap();
        $headers['Store'] = 'secondstore';

        $query = $this->getQuery('"nq_customer_closed_mask"');
        $response = $this->graphQlMutation($query, [], '', $headers);

        $this->assertNotEmpty($response['deleteNegotiableQuotes']);
        $this->assertArrayHasKey('items', $response['deleteNegotiableQuotes']['negotiable_quotes']);
        $this->assertArrayHasKey('page_info', $response['deleteNegotiableQuotes']['negotiable_quotes']);
        $this->assertArrayHasKey('total_count', $response['deleteNegotiableQuotes']['negotiable_quotes']);
    }

    /**
     * Testing that a quote for a different website is inaccessible
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/second_website.php
     * @magentoConfigFixture secondwebsitestore_store customer/account_share/scope 0
     * @magentoConfigFixture secondwebsite_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture secondwebsite_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testDeleteNegotiableQuoteForInvalidWebsite(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not find negotiable quotes with the following UIDs: nq_customer_mask');

        $this->storeManager->setCurrentStore('secondwebsitestore');
        $headers = $this->getHeaderMap();
        $headers['Store'] = 'secondwebsitestore';

        $query = $this->getQuery('"nq_customer_mask"');
        $this->graphQlMutation($query, [], '', $headers);
    }

    /**
     * Generates GraphQl mutation to delete negotiable quotes
     *
     * @param string $quoteIds
     * @return string
     */
    private function getQuery(string $quoteIds): string
    {
        return <<<MUTATION
mutation {
  deleteNegotiableQuotes(
    input: {
       quote_uids: [{$quoteIds}]
    }
  ) {
    negotiable_quotes {
      total_count
      page_info {
        page_size
        current_page
        total_pages
      }
      items {
      uid
      name
      status
      }
    }
  }
}
MUTATION;
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getHeaderMap(
        string $username = 'customercompany22@example.com',
        string $password = 'password'
    ): array {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    /**
     * Clean up the quotes
     *
     * @return void
     */
    private function deleteQuotes(): void
    {
        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->create();
        $quoteRepository = Bootstrap::getObjectManager()->create(QuoteRepository::class);
        $quotes = $quoteRepository->getList($searchCriteria)->getItems();
        foreach ($quotes as $quote) {
            $quote->delete();
        }
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }
}
