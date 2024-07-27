<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Task\ProductImport;

use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Sumedia\WinestroApi\ConfigInterface;
use Sumedia\WinestroApi\CustomFieldService;
use Sumedia\WinestroApi\Winestro\ArticleNumberParser;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;
use Sumedia\WinestroApi\RepositoryManagerInterface;
use Sumedia\WinestroApi\Winestro\Task\TaskInterface;

class ProductDataBuilder
{
    private array $properties = [];
    private array $measurements = [];
    private array $products = [];
    private ?string $currencyEntityId = null;

    public function __construct(
        private ConfigInterface $config,
        private RepositoryManagerInterface $repositoryManager,
        private LogManagerInterface $logManager,
        private ArticleNumberParser $articleNumberParser,
        private CustomFieldService $customFieldService,
        private Context $context
    ){}

    public function build(TaskInterface $task, array $articles): void
    {
        $this->properties = $this->config->get('properties');
        $this->measurements = $this->config->get('measurements');

        $this->products = [];
        foreach ($articles as $article) {
            try {
                $productData = [];
                $productNumber = $this->getProductNumber(
                    $article['articleNumber'],
                    $task['articleNumberFormat'],
                    $task['articleNumberYearSeparator'],
                    $task['articleNumberBottlingSeparator']
                );
                $product = $this->getProductByProductNumber($productNumber);
                $productData['id'] = null !== $product ? $product->getId() : Uuid::randomHex();
                $productData['productNumber'] = $productNumber;
                $productData['isCloseout'] = $this->isCloseout($product);
                $productData['shippingFree'] = $this->isShippingFree($task, $product, $article);
                $productData['active'] = $this->isActive($task, $product, $article);

                $productData['name'] = $this->getName($product, $article);
                $productData['stock'] = null !== $product ? $product->getStock() : 0;
                $productData['price'] = [$this->getPrice($product, $article)];
                $productData['ean'] = $this->getEan($product, $article);

                $productData['manufacturer'] = $this->getManufacturer($task, $product, $article);

                $productData['tax'] = ['id' => $this->getTaxId($product, $task['tax'])];
                $productData['weight'] = $article['weight'];
                $productData['description'] = $this->getDescription($task, $product, $article);
                $productData['packUnit'] = $article['unit'];
                $productData['unit'] = $this->getUnit($article);
                $productData['purchaseUnit'] = $this->getPurchaseUnit($article);
                $productData['referenceUnit'] = 1;

                $productData['deliveryTime'] = $this->getDeliveryTime($task, $product);

                $productData['visibilities'] = $this->getVisibilities($task, $product, $article);

                $productData['properties'] = [... $this->getProperties($article)];

                $productData['customFields'] = [... $this->getCustomFields($task, $product, $article)];

                if (null !== $product) {
                    $this->products[$productData['id']] = $productData;
                } else {
                    $this->products[] = $productData;
                }

            } catch (\Exception $exception) {
                $this->logManager->logException($exception);
            }
        }
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    private function getProductNumber(
        $articleNumber,
        $articleNumberFormat,
        $articleNumberYearSeparator,
        $bottlingSeparator
    ): string {
        return $this->articleNumberParser->WinestroArticleNumberToShopwareProductNumber(
            $articleNumber,
            $articleNumberFormat,
            $articleNumberYearSeparator,
            $bottlingSeparator
        );
    }

    private function getProductByProductNumber(string $productNumber): ?ProductEntity
    {
        return $this->repositoryManager->search('product',
            (new Criteria())->addFilter(new EqualsFilter('productNumber', $productNumber))
        )->first();
    }

    private function getName(ProductEntity|null $product, array $article): string
    {
        $name = $article['name'] ?: (null !== $product ? $product->getName() : '');
        if (empty($name)) {
            throw new \RuntimeException('name cannot be empty for article: ' . $article['articleNumber']);
        }
        return $name;
    }

    private function isCloseout(ProductEntity|null $product): bool
    {
        return null !== $product && !$product->getIsCloseout() ? false : true;
    }

    private function getTaxId(ProductEntity|null $product, string $taxId): string
    {
        return null !== $product && $product->getTaxId() !== $taxId ? $product->getTaxId() : $taxId;
    }

    private function getDescription(TaskInterface $task, ProductEntity|null $product, array $article): string
    {
        if (!$task['enabled']['description'] || (null !== $product && false === $product->getCustomFieldsValue('sumedia_winestro_product_switches_description'))) {
            if (null !== $product) {
                return $product->getDescription();
            }
        }
        return $article['shopDescription'];
    }

    private function isShippingFree(TaskInterface $task, ProductEntity|null $product, array $article): bool
    {
        if (!$task['enabled']['freeshipping'] || (null !== $product && false === $product->getCustomFieldsValue('sumedia_winestro_product_switches_free_shipping'))) {
            if (null !== $product) {
                return $product->getShippingFree();
            } else {
                return false;
            }
        }
        return (bool) $article['shippingFree'];
    }

    private function getDeliveryTime(TaskInterface $task, ProductEntity|null $product): array
    {
        return [
            'id' => null !== $product && $product->getDeliveryTimeId() !== $task['deliveryTime']
                ? $product->getDeliveryTimeId() : $task['deliveryTime']
        ];
    }

    private function isActive(TaskInterface $task, ProductEntity|null $product, array $article): bool
    {
        $active = true;

        if(empty($article['name'])) {
            $this->logManager->debug('Could not activate because name is not set on article '. $article['articleNumber']);
            $active = false;
        }
        if(empty($article['price']) && 0.00 !== $article['price']) {
            $this->logManager->debug('Could not activate because price is not set on article '. $article['articleNumber']);
            $active = false;
        }

        if(!empty($article['year']) && !empty($article['kind']) && !empty($article['quality']) && !empty($article['taste'])) {
            if (empty($article['alcohol']) && 0 != $article['alcohol']) {
                $this->logManager->debug('Could not activate because alcohol is not set on article '. $article['articleNumber']);
                $active = false;
            }
            if (empty($article['litre']) && 0 != $article['litre']) {
                $this->logManager->debug('Could not activate because litre is not set on article '. $article['articleNumber']);
                $active = false;
            }
        }

        if (!$task['enabled']['activestatus'] || (null !== $product && false === $product->getCustomFieldsValue('sumedia_winestro_product_switches_activestatus'))) {
            return null !== $product ? $product->getActive() : $active;
        }
        return $active;
    }

    public function getManufacturer(TaskInterface $task, ProductEntity|null $product, array $article): array
    {
        if (
            !$task['enabled']['manufacturer'] ||
            (null !== $product && false === $product->getCustomFieldsValue('sumedia_winestro_product_switches_manufacturer')) ||
            (empty($article['manufacturer']) || 0 == $article['manufacturerId'])
        ) {
            if (0 != $article['manufacturerId'] && empty($article['manufacturer'])) {
                $this->logManager->debug('needed manufacturer was empty on article ' . $article['articleNumber']);
            }
            $productManufacturerId = null !== $product ? ($product->getManufacturerId() ?: null) : null;
            return ['id' => null !== $product ? $productManufacturerId : $task['defaultManufacturer']];
        }

        $manufacturer = $this->repositoryManager->search('product_manufacturer',
            (new Criteria())->addFilter(new EqualsFilter('name', $article['manufacturer']))
        )->first();
        if (null === $manufacturer) {
            $id = Uuid::randomHex();
            $this->repositoryManager->create('product_manufacturer', [
                'id' => $id,
                'name' => $article['manufacturer']
            ]);
        } else {
            $id = $manufacturer->getId();
        }
        return ['id' => $id];
    }

    public function getPrice(ProductEntity|null $product, array $article): array
    {
        $currencyEntityId = $this->getCurrencyEntityId();

        if(null === $product) {
            return [
                'currencyId' => $currencyEntityId,
                'gross' => $article['price'],
                'net' => $article['price'] / (1 + $article['tax'] / 100),
                'linked' => false
            ];
        }

        $price = $product->getPrice();
        /** @var Price $priceElement */
        $priceElement = current($price->getElements());
        $listPrice = $priceElement->getListPrice();

        if ($listPrice) {
            return [
                'currencyId' => $currencyEntityId,
                'gross' => $priceElement->getGross(),
                'net' => $priceElement->getGross() / (1 + $article['tax'] / 100),
                'linked' => false,
                'listPrice' => [
                    "net" => $article['price'] / (1 + $article['tax'] / 100),
                    "gross" => $article['price'],
                    "linked" => true,
                    "currencyId" => $currencyEntityId
                ],
                'percentage' => [
                    'net' => 0,
                    'gross' => $priceElement->getGross() / $article['price'] * 100
                ]
            ];
        } else {
            return [
                'currencyId' => $currencyEntityId,
                'gross' => $article['price'],
                'net' => $article['price'] / (1 + $article['tax'] / 100),
                'linked' => false
            ];
        }
    }

    private function getEan(ProductEntity|null $product, array $article): ?string
    {
        return null !== $product && empty($article['ean']) ? $product->getEan() : $article['ean'];
    }

    private function getCurrencyEntityId(): string
    {
        if (null === $this->currencyEntityId) {
            $currencyEntity = $this->repositoryManager->search('currency',
                (new Criteria())->addFilter(new EqualsFilter('isoCode', 'EUR'))
            )->first();
            if (null === $currencyEntity) {
                throw new \RuntimeException('could not fetch currency entity');
            }
            $this->currencyEntityId = $currencyEntity->getId();
        }
        return $this->currencyEntityId;
    }

    private function getVisibilities(TaskInterface $task, ProductEntity|null $product, array $article): array
    {
        $salesChannelsIds = $task['visibleInSalesChannelsIds'];
        $visibilities = [];

        if (null !== $product) {
            $productVisibilities = $this->repositoryManager->search('product_visibility',
                (new Criteria())->addFilter(new EqualsFilter('productId', $product->getId()))
            );
            foreach ($productVisibilities as $productVisibilityId => $productVisibility) {
                if (false !== ($key = array_search($productVisibility->getSalesChannelId(), $salesChannelsIds))) {
                    unset($salesChannelsIds[$key]);
                }
                $visibilities[$productVisibilityId] = [
                    'id' => $productVisibilityId,
                    'salesChannelId' => $productVisibility->getSalesChannelId(),
                    'visibility' => $productVisibility->getVisibility(),
                ];
            }
        }

        foreach ($salesChannelsIds as $salesChannelId) {
            $visibilityId = Uuid::randomHex();
            $visibilities[$visibilityId] = [
                'id' => $visibilityId,
                'salesChannelId' => $salesChannelId,
                'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL
            ];
        }

        return $visibilities;
    }

    private function getUnit(array $article): array
    {
        switch ($article['unit']) {
            case 'Flasche':
                $unit = $this->getMeasurement('litre');
                break;
            default:
                $unit = $this->getMeasurement('kilo');
        }

        return [
            'id' => $unit->getId(),
        ];
    }

    private function getPurchaseUnit(array $article): string
    {
        if (0 < $article['litre']) {
            return $article['litre'];
        } else {
            return $article['fillingWeight'];
        }
    }

    private function getProperties(array $article) {
        return [
            ... $this->getProperty('country', $article),
            ... $this->getProperty('year', $article),
            ... $this->getProperty('kind', $article),
            ... $this->getProperty('quality', $article),
            ... $this->getProperty('taste', $article),
            ... $this->getProperty('region', $article),
            ... $this->getProperty('articleGroup', $article),
            ... $this->getProperty('ingredients', $article),
            ... $this->getProperty('sugar', $article),
            ... $this->getProperty('alcohol', $article),
            ... $this->getProperty('acid', $article),
            ... $this->getProperty('sulfits', $article),
            ... $this->getProperty('nuances', $article),
            ... $this->getProperty('awards', $article),
            ... $this->getProperty('bottles', $article),
            ... $this->getProperty('category', $article),
            ... $this->getProperty('allergens', $article),
            ... $this->getProperty('calories', $article),
            ... $this->getProperty('protein', $article),
            ... $this->getProperty('area', $article),
            ... $this->getProperty('location', $article),
            ... $this->getProperty('development', $article),
            ... $this->getProperty('drinkingTemperature', $article),
            ... $this->getProperty('fat', $article),
            ... $this->getProperty('unsaturatedFat', $article),
            ... $this->getProperty('carbonhydrates', $article),
            ... $this->getProperty('salt', $article),
            ... $this->getProperty('fiber', $article),
            ... $this->getProperty('vitamins', $article),
        ];
    }

    private function getProperty(string $propertyName, array $article): array
    {
        $propertyGroup = $this->getPropertyGroup($propertyName);
        if (null === $propertyGroup) {
            throw new \RuntimeException("property group $propertyName is not setted up");
        }

        if (null === $article[$propertyName] || (is_array($article[$propertyName]) && 0 === count($article[$propertyName]))) {
            return [];
        }

        $values = is_array($article[$propertyName])
            ? $article[$propertyName]
            : [$article[$propertyName]];

        $properties = [];
        foreach ($values as $value) {
            $value = (string) $value;
            $propertyGroupOption = $this->getPropertyOption($propertyGroup->getId(), $value);
            if (null === $propertyGroupOption) {
                $propertyGroupOptionId = $this->createPropertyOption($propertyGroup->getId(), $value);
            } else {
                $propertyGroupOptionId = $propertyGroupOption->getId();
            }
            $properties[] = [
                'id' => $propertyGroupOptionId,
                'name' => $value,
                'group' => ['id' => $propertyGroup->getId()]
            ];
        }

        return $properties;
    }

    private function getPropertyGroup(string $name): ?PropertyGroupEntity
    {
        return $this->repositoryManager->search(
            'property_group',
            new Criteria([$this->properties[$name]])
        )->first();
    }

    private function getPropertyOption(string $propertyGroupId, string $value): ?PropertyGroupOptionEntity
    {
        return $this->repositoryManager->search(
            'property_group_option',
            (new Criteria())->addFilter(new EqualsFilter('name', $value))
                ->addFilter(new EqualsFilter('groupId', $propertyGroupId))
        )->first();
    }

    private function createPropertyOption(string $propertGroupId, string $value): string
    {
        $id = Uuid::randomHex();
        $this->repositoryManager->create('property_group_option', [[
            'id' => $id,
            'groupId' => $propertGroupId,
            'name' => $value,
            'createdAt' => date('Y-m-d H:i:s')
        ]]);
        return $id;
    }

    private function getMeasurement($name)
    {
        $id = $this->measurements[$name];
        return $this->repositoryManager->search('unit', new Criteria([$id]))->first();
    }

    private function getCustomFields(TaskInterface $task, ProductEntity|null $product, array $article): array
    {
        $pos = 1;
        $fields = [
            ['sumedia_winestro_product_switches_activestatus',
                null !== $product && null !== $product->getCustomFieldsValue('sumedia_winestro_product_switches_activestatus')
                    ? $product->getCustomFieldsValue('sumedia_winestro_product_switches_activestatus') : $task['enabled']['activestatus']],
            ['sumedia_winestro_product_switches_manufacturer',
                null !== $product && null !== $product->getCustomFieldsValue('sumedia_winestro_product_switches_manufacturer')
                    ? $product->getCustomFieldsValue('sumedia_winestro_product_switches_manufacturer') : $task['enabled']['manufacturer']],
            ['sumedia_winestro_product_switches_free_shipping',
                null !== $product && null !== $product->getCustomFieldsValue('sumedia_winestro_product_switches_free_shipping')
                    ? $product->getCustomFieldsValue('sumedia_winestro_product_switches_free_shipping') : $task['enabled']['freeshipping']],
            ['sumedia_winestro_product_switches_description',
                null !== $product && null !== $product->getCustomFieldsValue('sumedia_winestro_product_switches_description')
                    ? $product->getCustomFieldsValue('sumedia_winestro_product_switches_description') : $task['enabled']['description']],

            ['sumedia_winestro_product_details_apnr', $article['apnr']],
            ['sumedia_winestro_product_details_article_number', $article['articleNumber']],
            ['sumedia_winestro_product_details_bottles', $article['bottles']],
            ['sumedia_winestro_product_details_best_before_date',
                is_string($article['bestBeforeDate']) && preg_match('#^\d{2}\.\d{2}\.\d{4}$#', $article['bestBeforeDate'])
                    ? date('Y-m-d', strtotime($article['bestBeforeDate'])) : null],
            ['sumedia_winestro_product_details_shelf_life', $article['shelfLife']],
            ['sumedia_winestro_product_details_e_label_free_text', $article['eLabelFreeText']],
            ['sumedia_winestro_product_details_description', $article['description']],
            ['sumedia_winestro_product_details_shop_description', $article['shopDescription']],
            ['sumedia_winestro_product_details_product_note', $article['productNote']],
            ['sumedia_winestro_product_details_bundle', is_array($article['bundle']) && count($article['bundle'])
                ? implode(',', array_keys($article['bundle'])) : null],

            ['sumedia_winestro_product_details_winestro_connection_id', $task['winestroConnectionId']]
        ];

        $return = [];
        foreach ($fields AS $params) {
            if (!empty($params[1])) {
                $return[$params[0]] = $params[1];
            }
        }
        return $return;
    }
}