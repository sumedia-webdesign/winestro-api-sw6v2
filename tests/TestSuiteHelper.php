<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Tests;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Plugin\Exception\PluginNotFoundException;
use Shopware\Core\Framework\Plugin\PluginLifecycleService;
use Shopware\Core\Framework\Plugin\PluginService;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TestSuiteHelper
{
    private $defaultCountryId = null;
    private $defaultSalesChannelId = null;
    private $defaultCurrencyId = null;
    private $defaultPaymentMethodId = null;
    private $defaultShippingMethodId = null;
    private $defaultCustomerGroupId = null;
    private $defaultTaxId = null;
    private $defaultSnippetSetId = null;
    private $defaultPassword = 'Password!123';
    private $systemConfig = array(
        array('configuration_key' => 'SumediaWinestroApi.config.winestroConnections', 'configuration_value' => '{"_value": {"a1ead7536e13c13350f2a17d37eed2ff": {"id": "a1ead7536e13c13350f2a17d37eed2ff", "url": "https://weinstore.net/xml/v20.0", "name": "Testshop", "shopId": 1, "userId": 2105, "secretId": "api-usr2105", "secretCode": "u5IC9B5TAD6MrpwFlubM7qGRk3yItj"}}}'),
        array('configuration_key' => 'SumediaWinestroApi.config.measurements', 'configuration_value' => '{"_value": {"kilo": "0190d41ca9bc7c9fa6e8c3c1c37a7a5c", "litre": "0190d41ca9bf7b1ab876de79f2d470d4", "volumepercent": "0190d41ca9c078cf8af375c5a1547008", "gramperhundret": "0190d41ca9be7fc0963ee00bfee59023"}}'),
        array('configuration_key' => 'SumediaWinestroApi.config.properties', 'configuration_value' => '{"_value": {"fat": "0190ec4e27817b89aa69d84961f48640", "acid": "0190ec4e2769743fb0aa32cdadd4baa8", "area": "0190ec4e277a767aa0b1759cf14d2735", "kind": "0190ec4e273070fbb6b1ac5554d398a1", "salt": "0190ec4e2790717c8370f97f2c6c9392", "year": "0190ec4e272f7fc08c99d0916d6da01b", "fiber": "0190ec4e279b7b0ca21081d185f003d6", "sugar": "0190ec4e27327d1a9c38fa9773fccd7c", "taste": "0190ec4e27357a81a46ac73792704078", "awards": "0190ec4e27567493a58c170042cf439b", "region": "0190ec4e272c72cd955e25df4cc0c73b", "alcohol": "0190ec4e27597d6a86b470461a51afdc", "bottles": "0190ec4e27557449a6cb3f0452257991", "country": "0190ec4e272a75eaac87b8c17d809d4c", "nuances": "0190ec4e27567493a58c1702947726ee", "protein": "0190ec4e277b7765966803e017658155", "quality": "0190ec4e272d7d0db1c747d51df92841", "sulfits": "0190ec4e275b7d59b90af19bf214fbb2", "calories": "0190ec4e276574e0be1235301bb8b7ed", "category": "0190ec4e27567493a58c16fe5f132c4d", "location": "0190ec4e277c7dc7bda001a03887aa30", "vitamins": "0190ec4e279f7d78967f6115bd8161a8", "allergens": "0190ec4e275773a39255118c21cbca4f", "development": "0190ec4e278b7aa992c0117f4944d7d5", "ingredients": "0190ec4e27337a87869ecf0d1e068b50", "articleGroup": "0190ec4e27327d1a9c38fa951e80de7b", "carbonhydrates": "0190ec4e278a7f3580f8882a655c49c3", "unsaturatedFat": "0190ec4e278a7f3580f8882893283d53", "drinkingTemperature": "0190ec4e27807914b752ce3044b3057c"}}'),
        array('configuration_key' => 'SumediaWinestroApi.config.salesChannels', 'configuration_value' => '{"_value": {"0190ad4501f0734e9a05e5ffa2b72331": {"salesChannelId": "0190ad4501f0734e9a05e5ffa2b72331", "winestroConnections": {"a1ead7536e13c13350f2a17d37eed2ff": {"paymentMapping": {"0190ad41d18270c7a5001b79ed826382": "3", "0190ad41d1a972989b8ca3105de2e86a": "1", "0190ad41d1d572908a214ec4785d4efa": "21", "0190ad41d20171d7bd66844eb191aebe": "6"}, "shippingMapping": {"0190ad41d2ca7241a89c75b01c52e8f5": "12", "0190ad41d2ca7241a89c75b01cf53d07": "12"}, "winestroConnectionId": "a1ead7536e13c13350f2a17d37eed2ff"}}}}}'),
        array('configuration_key' => 'SumediaWinestroApi.config.tasks', 'configuration_value' => '{"_value": {"019347c94e0074d9ac619c3b1431cbb1": {"id": "019347c94e0074d9ac619c3b1431cbb1", "tax": "0190ad41d3737035a58077b898c245d8", "name": "Produkte importieren", "type": "productImport", "enabled": {"enabled": true, "description": true, "activestatus": true, "freeshipping": true, "manufacturer": true}, "execute": ["019347c94e0074d9ac619c3caf169e5f", "019347c94e0074d9ac619c3d17b7101c", "019347c94e0074d9ac619c3eb2b70e04"], "extensions": [], "reducedTax": "0190ad41d3737035a58077b89939263f", "deliveryTime": "0190ad41d227727cae8c8166ee7d01c0", "articleNumberFormat": "[articlenumber+year+bottling]", "defaultManufacturer": "0190adbb006173a694149062a5a54d9a", "winestroConnectionId": "a1ead7536e13c13350f2a17d37eed2ff", "visibleInSalesChannelsIds": ["0190ad4501f0734e9a05e5ffa2b72331"], "articleNumberYearSeparator": "+", "articleNumberBottlingSeparator": "+"}, "019347c94e0074d9ac619c3caf169e5f": {"id": "019347c94e0074d9ac619c3caf169e5f", "name": "Produktbilder aktualisieren", "type": "productImageUpdate", "enabled": {"enabled": true}, "execute": [], "maxWidth": 1200, "maxHeight": 1200, "extensions": [], "mediaFolder": "0190ad41d5b37083a727009abbaafdba", "maxImageWidth": 860, "maxImageHeight": 860, "winestroConnectionId": "a1ead7536e13c13350f2a17d37eed2ff"}, "019347c94e0074d9ac619c3d17b7101c": {"id": "019347c94e0074d9ac619c3d17b7101c", "name": "Lagerbestand aktualisieren", "type": "productStock", "enabled": {"enabled": true}, "execute": [], "extensions": [], "sellingLimit": 0, "winestroConnectionId": "a1ead7536e13c13350f2a17d37eed2ff"}, "019347c94e0074d9ac619c3eb2b70e04": {"id": "019347c94e0074d9ac619c3eb2b70e04", "name": "Produktkategorien zuordnen", "type": "productCategoryAssignment", "enabled": {"enabled": true}, "execute": [], "extensions": [], "salesChannelId": "0190ad4501f0734e9a05e5ffa2b72331", "categoryIdentifier": "Winestro", "winestroConnectionId": "a1ead7536e13c13350f2a17d37eed2ff"}, "019347c94e0173adbf02bcffe5617ddb": {"id": "019347c94e0173adbf02bcffe5617ddb", "name": "Bestellungen exportieren", "type": "orderExport", "enabled": {"enabled": true, "sendWinestroEmail": true}, "execute": [], "extensions": [], "winestroConnectionId": "a1ead7536e13c13350f2a17d37eed2ff", "productsFromSalesChannelsIds": ["0190ad4501f0734e9a05e5ffa2b72331"], "productsFromWinestroConnectionIds": ["a1ead7536e13c13350f2a17d37eed2ff"]}, "019347c94e0173adbf02bd00e5d94cfc": {"id": "019347c94e0173adbf02bd00e5d94cfc", "name": "Bestellstatus aktualisieren", "type": "orderStatusUpdate", "enabled": {"enabled": true}, "execute": [], "extensions": [], "suppressEmail": true, "winestroConnectionId": "a1ead7536e13c13350f2a17d37eed2ff"}, "019347c94e02751b9da7fcab82b8581c": {"id": "019347c94e02751b9da7fcab82b8581c", "name": "NewsletterempfÃ€nger importieren", "type": "newsletterReceiverImport", "enabled": {"enabled": true}, "execute": [], "extensions": [], "salesChannelId": "0190ad4501f0734e9a05e5ffa2b72331", "winestroConnectionId": "a1ead7536e13c13350f2a17d37eed2ff"}}}'),
        array('configuration_key' => 'SumediaWinestroApi.config.cron', 'configuration_value' => '{"_value": {"019347ca8a6e7f978fdc31efe0d112a9": {"id": "019347ca8a6e7f978fdc31efe0d112a9", "name": "Produkte importieren", "times": "15m", "taskId": "019347c94e0074d9ac619c3b1431cbb1", "enabled": {"enabled": true}}, "019347ca8a6f7a9eb55f5fec67f10867": {"id": "019347ca8a6f7a9eb55f5fec67f10867", "name": "Bestellungen exportieren", "times": "5m", "taskId": "019347c94e0173adbf02bcffe5617ddb", "enabled": {"enabled": true}}, "019347ca8a6f7a9eb55f5fed72a5633c": {"id": "019347ca8a6f7a9eb55f5fed72a5633c", "name": "Bestellstatus aktualisieren", "times": "5m", "taskId": "019347c94e0173adbf02bd00e5d94cfc", "enabled": {"enabled": true}}, "019347ca8a707cc2901c58aabfd5fea7": {"id": "019347ca8a707cc2901c58aabfd5fea7", "name": "NewsletterempfÃ€nger importieren", "times": "1h", "taskId": "019347c94e02751b9da7fcab82b8581c", "enabled": {"enabled": true}}}}'),
        array('configuration_key' => 'SumediaWinestroApi.config.installationDone', 'configuration_value' => '{"_value": true}'),
        array('configuration_key' => 'SumediaWinestroApi.config.tasklock', 'configuration_value' => '{"_value": []}')
    );

    public function __construct(private ContainerInterface $container, private Context $context)
    {
        $countryRepository = $container->get('country.repository');
        $this->defaultCountryId = $countryRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('iso', 'DE'))
            , $this->context
        )->first()->getId();

        $currencyRepository = $container->get('currency.repository');
        $this->defaultCurrencyId = $currencyRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('isoCode', 'EUR'))
            , $this->context
        )->first()->getId();

        $paymentMethodRepository = $container->get('payment_method.repository');
        $this->defaultPaymentMethodId = $paymentMethodRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('technicalName', 'payment_prepayment'))
            , $this->context
        )->first()->getId();

        $shippingMethodRepository = $container->get('shipping_method.repository');
        $this->defaultShippingMethodId = $shippingMethodRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('technicalName', 'shipping_standard'))
            , $this->context
        )->first()->getId();

        $customerGroupRepository = $container->get('customer_group.repository');
        $this->defaultCustomerGroupId = $customerGroupRepository->search(
            new Criteria(), $this->context
        )->first()->getId();

        $taxRepository = $container->get('tax.repository');
        $this->defaultTaxId = $taxRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('name', 'Standard rate'))
            , $this->context
        )->first()->getId();

        $snippetSetRepository = $container->get('snippet_set.repository');
        $snippetSet = $snippetSetRepository->search(new Criteria(), $context)->first();
        $this->defaultSnippetSetId = $snippetSet->getId();

        $salesChannelRepository = $container->get('sales_channel.repository');
        $salesChannel = $salesChannelRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('name', 'Default Sales Channel'))
            , $this->context
        )->first();
        if (!$salesChannel) {
            $this->defaultSalesChannelId = $this->createSalesChannel();
        } else {
            $this->defaultSalesChannelId = $salesChannel->getId();
        }
    }

    public function setupPlugin(): void
    {
        /** @var PluginService $pluginService */
        $pluginService = $this->container->get(PluginService::class);
        $pluginActive = true;
        try {
            $plugin = $pluginService->getPluginByName('SumediaWinestroApi', $this->context);
        } catch(PluginNotFoundException $e) {
            $pluginActive = false;
        }

        if (!$pluginActive) {
            /** @var PluginLifecycleService $pluginLifecycleService */
            $pluginLifecycleService = $this->container->get(PluginLifecycleService::class);
            if (!$plugin->getInstalledAt()) {
                $pluginLifecycleService->installPlugin($plugin, $this->context);
            }
            if (!$plugin->getActive()) {
                $pluginLifecycleService->activatePlugin($plugin, $this->context);
            }
        } else {
            $this->container->get('plugin.repository')->update([[
                'id' => $plugin->getId(),
                'installedAt' => (new \DateTime())->format('Y-m-d H:i:s')
            ]], $this->context);
        }

        foreach ($this->systemConfig as $systemConfig) {
            /** @var SystemConfigService $systemConfigService */
            $systemConfigService = $this->container->get(SystemConfigService::class);
            $config = $systemConfigService->get($systemConfig['configuration_key']);
            if (null === $config) {
                $systemConfigService->set($systemConfig['configuration_key'], $systemConfig['configuration_value']);
            }
        }
    }

    public function createSalesChannel(array $data = []): string
    {
        $salesChannelRepository = $this->container->get('sales_channel.repository');

        $salesChannelData = [
            'id' => Uuid::randomHex(),
            'name' => 'Default Sales Channel',
            'typeId' => Defaults::SALES_CHANNEL_TYPE_API,
            'accessKey' => Uuid::randomHex(),
            'customerGroupId' => $this->defaultCustomerGroupId,
            'languages' => [
                ['id' => Defaults::LANGUAGE_SYSTEM],
            ],
            'currencyId' => $this->defaultCurrencyId,
            'paymentMethodId' => $this->defaultPaymentMethodId,
            'shippingMethodId' => $this->defaultShippingMethodId,
            'countryId' => $this->defaultCountryId,
            'navigationCategoryId' => '0190ad41d39a7003bec15544ef059149',
            'paymentMethods' => [[
                'id' => $this->defaultPaymentMethodId,
                'translations' => [
                    '2fbb5fe2e29a4d70aa5854ce7ce3e20b' => [
                        'name' => 'Default Payment Method',
                    ]
                ]
            ]],
            'shippingMethods' => [[
                'id' => $this->defaultShippingMethodId,
                'deliveryTimeId' => '0190ad41d227727cae8c8166ee7d01c0',
                'translations' => [
                    '2fbb5fe2e29a4d70aa5854ce7ce3e20b' => [
                        'name' => 'Default Shipping Method',
                    ]
                ]
            ]],
            'domains' => [
                [
                    'url' => 'https://localhost',
                    'languageId' => Defaults::LANGUAGE_SYSTEM,
                    'currencyId' => $this->defaultCurrencyId,
                    'snippetSetId' => $this->defaultSnippetSetId,
                ],
            ],
            'active' => true,
        ];

        $salesChannelData = array_replace_recursive($salesChannelData, $data);

        $salesChannelRepository->create([$salesChannelData], $this->context);

        return $salesChannelData['id'];
    }

    public function createOrder(array $data = [], string $customerId = null, string $billingAddressId = null, string $productId = null): string
    {
        $orderRepository = $this->container->get('order.repository');
        $productRepository = $this->container->get('product.repository');

        $lastOrderId = $orderRepository->search(
            (new Criteria())->addSorting(new FieldSorting('orderNumber', 'DESC'))
        , $this->context)->first();
        $orderNumber = (string) (null !== $lastOrderId ? $lastOrderId->getOrderNumber() + 1 : 1);

        $productNumber = $productRepository->search(new Criteria([$productId]), $this->context)->first()->getProductNumber();

        $orderId = Uuid::randomHex();
        $lineItemId = Uuid::randomHex();

        $orderData = [
            'id' => $orderId,
            'stateId' => '0190ad41decb73fa9e362dba380a4a35',
            'orderNumber' => $orderNumber,
            'billingAddressId' => $billingAddressId,
            'orderDateTime' => (new \DateTime())->format('Y-m-d H:i:s'),
            'currencyFactor' => 1,
            'price' => [
                'rawTotal' => 119,
                'netPrice' => 100,
                'totalPrice' => 119,
                'calculatedTaxes' => [
                    [
                        'tax' => 19,
                        'taxRate' => 19,
                        'price' => 100,
                    ],
                ],
                'taxRules' => [
                    [
                        'taxRate' => 19,
                        'percentage' => 100,
                    ],
                ],
                'positionPrice' => 100,
                'taxStatus' => 'gross',
            ],
            'currencyId' => $this->defaultCurrencyId, // Standardwährung
            'salesChannelId' => $this->defaultSalesChannelId,
            'shippingCosts' => [
                'unitPrice' => 0,
                'quantity' => 1,
                'totalPrice' => 0,
                'calculatedTaxes' => [
                    [
                        'tax' => 0,
                        'price' => 0,
                        'taxRate' => 0,
                    ],
                ],
                'taxRules' => [
                    [
                        'taxRate' => 19,
                        'percentage' => 100,
                    ],
                ],
            ],
            'lineItems' => [
                [
                    'id' => $lineItemId,
                    'productId' => $productId,
                    'referencedId' => $productId,
                    'label' => 'Product',
                    'identifier' => 'line-item-1',
                    'quantity' => 1,
                    'type' => 'product',
                    'price' => [
                        'unitPrice' => 100,
                        'totalPrice' => 100,
                        'quantity' => 1,
                        'calculatedTaxes' => [
                            [
                                'tax' => 19,
                                'taxRate' => 19,
                                'price' => 19,
                            ],
                        ],
                        'taxRules' => [
                            [
                                'taxRate' => 19,
                                'percentage' => 100,
                            ],
                        ],
                    ],
                    'payload' => [
                        'productId' => $productId,
                        'productNumber' => $productNumber
                    ],
                ],
            ],
            'transactions' => [
                [
                    'id' => Uuid::randomHex(),
                    'stateId' => '0190ad41e2ee70af8b793755f5c0dc53',
                    'paymentMethodId' => $this->defaultPaymentMethodId, // ID der Zahlungsmethode
                    'amount' => [
                        'unitPrice' => 119,
                        'quantity' => 1,
                        'calculatedTaxes' => [
                            [
                                'tax' => 0.95,
                                'price' => 5.99,
                                'taxRate' => 19
                            ]
                        ],
                        'totalPrice' => 119,
                        'taxRules' => []
                    ],
                ],
            ],
            'itemRounding' => [
                'decimals' => 2,
                'interval' => 2
            ],
            'totalRounding' => [
                'decimals' => 2,
                'interval' => 2
            ],
            'deliveries' => [
                [
                    'id' => Uuid::randomHex(),
                    'stateId' => '0190ad41e03e71eb8d5d42f4dba2f98b',
                    'shippingDateEarliest' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'shippingDateLatest' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'shippingMethodId' => $this->defaultShippingMethodId,
                    'shippingCosts' => [
                        'unitPrice' => 0,
                        'quantity' => 1,
                        'totalPrice' => 0,
                        'calculatedTaxes' => [
                            [
                                'tax' => 0.95,
                                'price' => 5.99,
                                'taxRate' => 19
                            ]
                        ],
                        'taxRules' => [
                            [
                                'taxRate' => 19,
                                'percentage' => 100,
                            ],
                        ]
                    ],
                    'shippingOrderAddress' => [
                        'id' => $billingAddressId,
                        'customerId' => $customerId,
                        'firstName' => 'Max',
                        'lastName' => 'Mustermann',
                        'street' => 'Musterstraße 1',
                        'zipcode' => '12345',
                        'city' => 'Musterstadt',
                        'countryId' => $this->defaultCountryId,
                    ]
                ],
            ],
        ];

        $orderData = array_replace_recursive($orderData, $data);
        $orderRepository->create([$orderData], $this->context);

        return $orderData['id'];
    }

    public function updateOrder(array $data): void
    {
        $orderRepository = $this->container->get('order.repository');
        $orderRepository->update([$data], $this->context);
    }

    public function deleteOrder(string $orderId): void
    {
        $orderRepository = $this->container->get('order.repository');
        $orderRepository->delete([['id' => $orderId]], $this->context);
    }

    public function createBillingAddress(array $data = [], string $customerId = null): string
    {
        $addressRepository = $this->container->get('customer_address.repository');
        $billingAddressId = Uuid::randomHex();

        $addressData = [
            'id' => $billingAddressId,
            'customerId' => $customerId,
            'firstName' => 'Max',
            'lastName' => 'Mustermann',
            'street' => 'Musterstraße 1',
            'zipcode' => '12345',
            'city' => 'Musterstadt',
            'countryId' => $this->defaultCountryId,
        ];

        $addressData = array_replace_recursive($addressData, $data);

        $addressRepository->create([$addressData], $this->context);

        return $addressData['id'];
    }

    public function createShippingAddress(array $data = [], string $customerId = null): string
    {
        $shippingAddressId = Uuid::randomHex();
        $shippingAddressRepository = $this->container->get('customer_address.repository');

        $addressData = [
            'id' => $shippingAddressId,
            'customerId' => $customerId,
            'firstName' => 'Anna',
            'lastName' => 'Musterfrau',
            'street' => 'Musterweg 5',
            'zipcode' => '54321',
            'city' => 'Lieferstadt',
            'countryId' => $this->defaultCountryId,
        ];

        $addressData = array_replace_recursive($addressData, $data);

        $shippingAddressRepository->create([$addressData], $this->context);

        return $addressData['id'];
    }

    public function createCustomer(array $data = []): string
    {
        $customerId = Uuid::randomHex();
        $customerRepository = $this->container->get('customer.repository');

        $customer = $customerRepository->search(
            (new Criteria)->addSorting(new FieldSorting('customerNumber', 'DESC'))
            , $this->context
        )->first();
        $customerNumber = (string) (null !== $customer ? $customer->getCustomerNumber() + 1 : 1);

        $customerData = [
            'id' => $customerId,
            'salesChannelId' => $this->defaultSalesChannelId,
            'defaultPaymentMethodId' => $this->defaultPaymentMethodId,
            'groupId' => $this->defaultCustomerGroupId,
            'customerNumber' => $customerNumber,
            'defaultBillingAddress' => [
                'id' => Uuid::randomHex(),
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'street' => 'Musterstraße 1',
                'zipcode' => '12345',
                'city' => 'Musterstadt',
                'countryId' => $this->defaultCountryId,
            ],
            'defaultShippingAddress' => [
                'id' => Uuid::randomHex(),
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'street' => 'Musterstraße 1',
                'zipcode' => '12345',
                'city' => 'Musterstadt',
                'countryId' => $this->defaultCountryId,
            ],
            'email' => 'max.mustermann@example.com',
            'password' => $this->defaultPassword,
            'firstName' => 'Max',
            'lastName' => 'Mustermann',
            'active' => true,
        ];

        $customerData = array_replace_recursive($customerData, $data);

        $customerRepository->create([$customerData], $this->context);

        return $customerData['id'];
    }

    public function createProduct(array $data = []): string
    {
        $productId = Uuid::randomHex();
        $productRepository = $this->container->get('product.repository');
        $product = $productRepository->search(
            (new Criteria([$productId]))->addSorting(new FieldSorting('productNumber', 'DESC'))
            , $this->context
        )->first();
        $productNumber = (string) (null !== $product ? $product->getProductNumber() + 1 : 1);

        $productData = [
            'id' => $productId,
            'name' => 'Test Product',
            'productNumber' => $productNumber,
            'stock' => 100,
            'price' => [
                [
                    'currencyId' => $this->defaultCurrencyId,
                    'gross' => 19.99,
                    'net' => 16.80,
                    'linked' => true,
                ],
            ],
            'tax' => [
                'id' => $this->defaultTaxId,
            ],
            'active' => true,
            'visibility' => [
                [
                    'salesChannelId' => $this->defaultSalesChannelId,
                    'visibility' => 30,
                ],
            ],
        ];

        $productData = array_replace_recursive($productData, $data);

        // Produkt erstellen
        $productRepository->create([$productData], $this->context);

        return $productData['id'];
    }
}