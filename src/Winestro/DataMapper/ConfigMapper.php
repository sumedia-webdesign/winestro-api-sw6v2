<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\DataMapper;

class ConfigMapper implements DataMapperInterface
{
    const API_URL                       = 'apiUrl';
    const SHOP_ID                       = 'shopId';
    const USER_ID                       = 'userId';
    const CLIENT_ID                     = 'clientId';
    const CLIENT_SECRET                 = 'clientSecret';
    const SALE_CHANNEL_IDS              = 'saleChannelIds';
    const FREE_SHIPPING_ACTIVATED       = 'freeShippingActivated';
    const FREE_SHIPPING_ONLY_WBO_CART   = 'freeShippingOnlyWboCart';
    const MANUFACTURER_ENABLED          = 'manufacturerEnabled';
    const MANUFACTURER_ID               = 'manufacturerId';
    const DELIVERY_TIME_ID              = 'deliveryTimeId';
    const TAX_ID                        = 'taxId';
    const REDUCED_TAX_ID                = 'reducedTaxId';
    const MEDIA_DIRECTORY               = 'mediaDirectory';
    const MEDIA_MAX_WIDTH               = 'mediaMaxWidth';
    const MEDIA_MAX_HEIGHT              = 'mediaMaxHeight';
    const DEBUG_ENABLED                 = 'debugEnabled';
    const DEBUG_TRANSMITTION_ENABLED    = 'transmittionLogEnabled';
    const PROPERTY_YEAR_ID              = 'yearPropertyId';
    const PROPERTY_YEAR_AUTO_ADD        = 'yearPropertyAutoAdd';
    const PROPERTY_CULTIVATION_ID       = 'cultivationPropertyId';
    const PROPERTY_CULTIVATIONA_AUTO_ADD = 'cultivationPropertyAutoAdd';
    const PROPERTY_TASTE_ID             = 'tastePropertyId';
    const PROPERTY_TASTE_AUTO_ADD       = 'tastePropertyAutoAdd';
    const PROPERTY_QUALITY_ID           = 'qualityPropertyId';
    const PROPERTY_QUALITY_AUTO_ADD     = 'qualityPropertyAutoAdd';
    const PROPERTY_GRAPE_ID             = 'grapePropertyId';
    const PROPERTY_GRAPE_AUTO_ADD       = 'grapePropertyAutoAdd';
    const PROPERTY_CATEGORY_ID          = 'categoryPropertyId';
    const PROPERTY_CATEGORY_AUTO_ADD    = 'categoryPropertyAutoAdd';
    const UNIT_ID                       = 'unitId';
    const UNIT_AUTO_ADD                 = 'unitAutoAdd';
    const UNIT_KILO_ID                  = 'unitKiloId';
    const UNIT_KILO_AUTO_ADD            = 'unitKiloAutoAdd';
    const SET_CATEGORIES_ACTIVATED      = 'setCategoriesActivated';
    const STOCK_ENABLED                 = 'stockEnabled';
    const STOCK_MINIMAL                 = 'stockMinimal';
    const STOCK_DEFAULT                 = 'stockDefault';
    const AUTOMATIC_ORDER_CONFIRMATION_MAIL_ENABLED = 'orderConfirmationEmailEnabled';
    const ARTICLE_NUMBER_FORMAT         = 'articleNumberFormat';
    const PRODUCT_DATA_ACTIVATED        = 'productDataActivated';
    const COUNT_BOTTLES                 = 'countBottles';

    private array $map = [];

    public function __construct()
    {
        $ref = new \ReflectionClass(self::class);
        foreach ($ref->getConstants() as $key => $value) {
            $this->map[$key] = $value;
        }
    }

    public function mapKey(string $key): string
    {
        return $this->map[$key];
    }

    public function toArray(): array
    {
        return $this->map;
    }
}