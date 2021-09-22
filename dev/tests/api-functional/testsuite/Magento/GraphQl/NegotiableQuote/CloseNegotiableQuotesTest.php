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
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\NegotiableQuoteGraphQl\Model\NegotiableQuote\ResourceModel\QuoteIdMask;
use Magento\Quote\Model\QuoteRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\GraphQl\NegotiableQuote\Fixtures\CustomerRequestNegotiableQuote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage to close negotiable quotes.
 */
class CloseNegotiableQuotesTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var NegotiableQuoteManagementInterface
     */
    private $negotiableQuoteManagement;

    /**
     * @var NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var QuoteIdMask
     */
    private $quoteIdMaskResource;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->negotiableQuoteManagement = $objectManager->get(NegotiableQuoteManagementInterface::class);
        $this->negotiableQuoteRepository = $objectManager->get(NegotiableQuoteRepositoryInterface::class);
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);
        $this->customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
        $this->quoteIdMaskResource = $objectManager->get(QuoteIdMask::class);
    }

    /**
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testCloseNegotiableQuote(): void
    {
        $customer = $this->customerRepository->get('customercompany22@example.com');
        $quotes = $this->negotiableQuoteRepository->getListByCustomerId($customer->getId());
        $quoteId = array_key_last($quotes);

        $query = $this->getQuery('"nq_customer_mask"');
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        $this->assertArrayHasKey('closed_quotes', $response['closeNegotiableQuotes']);
        $this->assertArrayHasKey('negotiable_quotes', $response['closeNegotiableQuotes']);
        $this->assertNotEmpty($response['closeNegotiableQuotes']);
        //Closed one quote
        $this->assertCount(1, $response['closeNegotiableQuotes']['closed_quotes']);
        $this->assertEquals('nq_customer_mask', $response['closeNegotiableQuotes']['closed_quotes'][0]['uid']);
        $this->assertEquals('quote_customer_send', $response['closeNegotiableQuotes']['closed_quotes'][0]['name']);
        $this->assertCount(2, $response['closeNegotiableQuotes']['closed_quotes'][0]['history']);

        $closedQuotesHistory = $response['closeNegotiableQuotes']['closed_quotes'][0]['history'];
        $this->assertEquals('CREATED', array_first($closedQuotesHistory)['change_type']);
        $this->assertEquals('CLOSED', array_last($closedQuotesHistory)['change_type']);

        $this->assertEquals(1, $response['closeNegotiableQuotes']['negotiable_quotes']['total_count']);
        $negotiableQuoteItems = $response['closeNegotiableQuotes']['negotiable_quotes']['items'];
        $this->assertEquals('nq_customer_mask', $negotiableQuoteItems[0]['uid']);
        $this->assertEquals('quote_customer_send', $negotiableQuoteItems[0]['name']);
        $this->assertEquals('CLOSED', $negotiableQuoteItems[0]['status']);
        $this->assertCount(2, $negotiableQuoteItems[0]['history']);
        $responseHistory = array_last($negotiableQuoteItems[0]['history']);
        $this->assertEquals('CLOSED', array_last($negotiableQuoteItems[0]['history'])['change_type']);
        $this->assertEquals('CREATED', array_first($negotiableQuoteItems[0]['history'])['change_type']);
    }

    /**
     *  Test that multiple negotiable quotes can be closed
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
    public function testCloseMultipleNegotiableQuotes(array $negotiableQuoteData): void
    {
        try {
            /** @var CustomerRequestNegotiableQuote $requestNegotiableQuoteFixture */
            $requestNegotiableQuoteFixture = Bootstrap::getObjectManager()->create(CustomerRequestNegotiableQuote::class);
            $requestedNegotiableQuotes = $requestNegotiableQuoteFixture->requestNegotiableQuotes
            (
                ['email' => 'customercompany22@example.com', 'password' => 'password'],
                $negotiableQuoteData
            );
            $negotiableQuoteIds = [];
            foreach ($requestedNegotiableQuotes as $requestedNegotiableQuote) {
                array_push($negotiableQuoteIds, $requestedNegotiableQuote['uid']);
            }

            $query =  <<<MUTATION
mutation {
  closeNegotiableQuotes(
    input: {
      quote_uids: ["{$negotiableQuoteIds[0]}", "{$negotiableQuoteIds[1]}"]
    }
  ) {
    closed_quotes {
      uid,
      status,
      name,
      created_at
      history {
       change_type
        changes {
          statuses {
            changes { new_status old_status }}
      }
    }
  }
    negotiable_quotes(sort:{ sort_direction:ASC sort_field:QUOTE_NAME }) {
        total_count
        items { uid name status history { change_type } }
    }
  }
}
MUTATION;
            $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
            $this->assertArrayHasKey('closed_quotes', $response['closeNegotiableQuotes']);
            $this->assertArrayHasKey('negotiable_quotes', $response['closeNegotiableQuotes']);
            $this->assertNotEmpty($response['closeNegotiableQuotes']);
            $this->assertCount(2, $response['closeNegotiableQuotes']['closed_quotes']);
            $this->assertEquals(2, $response['closeNegotiableQuotes']['negotiable_quotes']['total_count']);
            $negotiableQuoteItems = $response['closeNegotiableQuotes']['negotiable_quotes']['items'];
            $this->assertCount(2, $response['closeNegotiableQuotes']['negotiable_quotes']['items']);
            foreach($negotiableQuoteItems as $negotiableQuoteItem) {
                $this->assertEquals('CLOSED', $negotiableQuoteItem['status']);
                $expectedQuoteHistory = [
                    ['change_type' => 'CREATED'],
                    ['change_type' => 'CLOSED' ]
                ];
                $this->assertResponseFields($negotiableQuoteItem['history'],$expectedQuoteHistory);
            }
            foreach($negotiableQuoteItems as $index => $negotiableQuoteItem) {
                $this->assertEquals($requestedNegotiableQuotes[$index]['name'], $negotiableQuoteItems[$index]['name']);
            }

        } finally {
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
                    ]
                ]
            ]
        ];
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
    public function testCloseNegotiableQuoteInvalidData(
        string $customerEmail,
        string $customerPassword,
        string $message
    ): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($message);

        $query = $this->getQuery('"nq_customer_mask"');
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
    public function testCloseNegotiableQuoteWithNoCustomerToken(): void
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
    public function testCloseNegotiableQuoteNoModuleEnabled(): void
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
    public function testCloseNegotiableQuoteCustomerNoCompany(): void
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
    public function testCloseNegotiableQuoteNoCompanyFeatureEnabled(): void
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
    public function testCloseNegotiableQuoteNoManagePermissions(): void
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
    public function testCloseNegotiableQuoteUnownedQuote(): void
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
    public function testCloseNegotiableQuoteNonNegotiable(): void
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
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_closed.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testCloseNegotiableQuoteBadStatus(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'The quotes with the following UIDs have a status that does not allow them to be closed: '
            . 'nq_customer_closed_mask'
        );

        $query = $this->getQuery('"nq_customer_closed_mask"');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/two_simple_products_for_quote.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_with_customer_for_quote.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_with_declined_status.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     */
    public function testCloseNegotiableQuoteDeclinedStatus(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'The quotes with the following UIDs have a status that does not allow them to be closed: '
            . 'nq_customer_declined_mask'
        );
        $username = 'email@companyquote.com';
        $password = 'password';
        $query = $this->getQuery('"nq_customer_declined_mask"');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap($username, $password));
    }

    /**
     * Testing that a quote for a different store on the same website is accessible
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/second_store.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testCloseNegotiableQuoteForSecondStore(): void
    {
        $this->storeManager->setCurrentStore('secondstore');
        $headers = $this->getHeaderMap();
        $headers['Store'] = 'secondstore';

        $query = $this->getQuery('"nq_customer_mask"');
        $response = $this->graphQlMutation($query, [], '', $headers);

        $this->assertNotEmpty($response['closeNegotiableQuotes']);
        $this->assertArrayHasKey('closed_quotes', $response['closeNegotiableQuotes']);
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
    public function testCloseNegotiableQuoteForInvalidWebsite(): void
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
     * Schema mutation to close negotiable quotes.
     *
     * @param string $quoteIds
     * @return string
     */
    private function getQuery(string $quoteIds): string
    {
        return <<<MUTATION
mutation {
  closeNegotiableQuotes(
    input: {
      quote_uids: [{$quoteIds}]
    }
  ) {
    closed_quotes {
      uid,
      status,
      name,
      created_at
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
}
