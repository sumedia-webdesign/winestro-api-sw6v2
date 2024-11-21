<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Subscriber;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\Locale\LocaleEntity;
use Sumedia\WinestroApi\RepositoryManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @deprecated
 */
class ProductExtensionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RepositoryManagerInterface $repositoryManager
    ){}

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_LOADED_EVENT => 'onProductsLoaded'
        ];
    }

    public function onProductsLoaded(EntityLoadedEvent $event): void
    {
        foreach ($event->getEntities() as $entity) {
            $entity->addExtension('wbo_article', new ArrayStruct($this->fetchData($entity, $event->getContext())));
        }
    }

    public function fetchData(ProductEntity $product, Context $context): array
    {
        return [
            'id' => $product->getId(),
            'articleNumber' => $product->getCustomFieldsValue('sumedia_winestro_product_details_article_number'),
            'articleNumberFormat' => '[]',
            'productNumber' => $product->getProductNumber(),
            'name' => $product->getName(),
            'description' => $product->getCustomFieldsValue('sumedia_winestro_product_details_description'),
            'type' => null,
            'typeId' => null,
            'color' => null,
            'country' => $this->getOption($product,  'Country', $context),
            'region' => $this->getOption($product,  'Region', $context),
            'stockWarning' => null,
            'weight' => $product->getWeight(),
            'price' => $product->getPrice(),
            'taxPercent' => $product->getTax()->getTaxRate(),
            'isFreeShipping' => $product->getShippingFree(),
            'noLitrePrice' => null,
            'notice' => $product->getCustomFieldsValue('sumedia_winestro_product_details_product_notice'),
            'groupId' => null,
            'isWine' => null,
            'bottles' => $product->getCustomFieldsValue('sumedia_winestro_product_details_bottles'),
            'shopnotice' => $product->getCustomFieldsValue('sumedia_winestro_product_details_shop_description'),
            'kiloprice' => null,
            'fillingWeight' => null,
            'waregroup' => null,
            'allergens' => $this->getOption($product,  'Allergens', $context),
            'apnr' => $product->getCustomFieldsValue('sumedia_winestro_product_details_apnr'),
            'awards' => $this->getOption($product,  'Awards', $context),
            'caloricValue' => $this->getOption($product,  'Calories', $context),
            'cultivation' => null,
            'location' => $this->getOption($product,  'Location', $context),
            'development' => $this->getOption($product,  'Development', $context),
            'drinkingTemperature' => $this->getOption($product,  'Drinking Temperature', $context),
            'expertise' => null,
            'grounds' => null,
            'hasSulfite' => 0 < $this->getOption($product,  'Sulfits', $context),
            'isDrunken' => null,
            'isStorable' => null,
            'kind' => $this->getOption($product,  'Kind', $context),
            'alcohol' => $this->getOption($product,  'Alcohol', $context),
            'litre' => $product->getPurchaseUnit(),
            'litrePrice' => null,
            'nuances' => $this->getOption($product,  'Nuances', $context),
            'protein' => $this->getOption($product,  'Protein', $context),
            'quality' => $this->getOption($product,  'Quality', $context),
            'sugar' => $this->getOption($product,  'Sugar', $context),
            'taste' => $this->getOption($product,  'Taste', $context),
            'year' => $this->getOption($product,  'Year', $context),
            'acid' => $this->getOption($product,  'Acid', $context),
            'image1' => null,
            'image2' => null,
            'image3' => null,
            'image4' => null,
            'bigImage1' => null,
            'bigImage2' => null,
            'bigImage3' => null,
            'bigImage4' => null,
            'category' => $this->getOption($product,  'Category', $context),
            'manufacturer' => $this->getManufacturer((string) $product->getManufacturerId(), $context),
            'unitId' => null,
            'unit' => null,
            'unitQuantity' => null,
            'ean' => $product->getEan(),
            'createdAt' => $product->getCreatedAt(),
            'updatedAt' => $product->getUpdatedAt(),
            'importedAt' => $product->getUpdatedAt(),
            'stock' => $product->getStock(),
            'stockDate' => $product->getCustomFieldsValue('sumedia_winestro_product_details_stock_update_date'),
            'bundle' => $product->getCustomFieldsValue('sumedia_winestro_product_details_bundle')
        ];
    }

    private function getOption(ProductEntity $product, string $name, Context $context): mixed
    {
        $isoCode = $this->getLocale($context->getLanguageId(), $context)->getCode();
        $map = [
            'Country' => 'Land',
            'Year' => 'Jahrgang',
            'Kind' => 'Rebsorte',
            'Quality' => 'Qualität',
            'Taste' => 'Geschmack',
            'Region' => 'Region',
            'Article Group' => 'Artikelgruppe',
            'Ingredients' => 'Zutat',
            'Sugar' => 'Zucker',
            'Alcohol' => 'Alkohol',
            'Acid' => 'Säure',
            'Sulfits' => 'Sulfite',
            'Nuances' => 'Nuancen',
            'Awards' => 'Auszeichnung',
            'Bottles included' => 'Flaschenanzahl',
            'Category' => 'Kategorie',
            'Allergens' => 'Allergene',
            'Calories' => 'Kalorien',
            'Protein' => 'Eiweiß',
            'Area' => 'Anbaugebiet',
            'Location' => 'Lage',
            'Development' => 'Ausbau',
            'Drinking Temperature' => 'Trinktemperatur',
            'Fat' => 'Fettsäuren',
            'Unsaturated fat' => 'Ungesättigte Fettsäuren',
            'Carbonhydrates' => 'Kohlenhydrate',
            'Salt' => 'Salz',
            'Fiber' => 'Ballaststoffe',
            'Vitamins' => 'Vitamine'
        ];
        $name = 'de-DE' === $isoCode ? $map[$name] : $name;
        foreach ($product->getOptions() ?: [] as $option) {
            if ($option->getGroup()->getName() === $name) {
                return $option->getName();
            }
        }
        return null;
    }

    private function getLocale(string $languageId, Context $context): LocaleEntity
    {
        return $this->repositoryManager->search('language',
            (new Criteria([$languageId]))->addAssociation('locale'),
            $context
        )->first()->getLocale();
    }

    private function getManufacturer(string $manufacturerId, Context $context): mixed
    {
        if (empty($manufacturerId)) {
            return null;
        }

        $manufacturer = $this->repositoryManager->search('product_manufacturer',
            (new Criteria([$manufacturerId])),
            $context
        )->first();
        if ($manufacturer) {
            return $manufacturer->getName();
        }
        return null;
    }
}
