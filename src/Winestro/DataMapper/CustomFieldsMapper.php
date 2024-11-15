<?php declare(strict_types=1);

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\WinestroApi\Winestro\DataMapper;

class CustomFieldsMapper implements DataMapperInterface
{
    const FIELD_SET_PRODUCT_DETAILS = 'sumedia_winestro_product_details';
    const FIELD_SET_PRODUCT_SWITCHES = 'sumedia_winestro_product_switches';
    const FIELD_SET_ORDER_DETAILS = 'sumedia_winestro_order_details';

    const FIELD_PRODUCT_DETAILS_APNR = 'sumedia_winestro_product_details.apnr';
    const FIELD_PRODUCT_DETAILS_ARTICLE_NUMBER = 'sumedia_winestro_product_details.article_number';
    const FIELD_PRODUCT_DETAILS_BOTTLES = 'sumedia_winestro_product_details.bottiles';
    const FIELD_PRODUCT_DETAILS_BEST_BEFORE_DATE = 'sumedia_winestro_product_details.best_before_date';
    const FIELD_PRODUCT_DETAILS_SHELF_LIFE = 'sumedia_winestro_product_details.shelf_life';
    const FIELD_PRODUCT_DETAILS_E_LABEL_FREE_TEXT = 'sumedia_winestro_product_details.e_label_free_text';
    const FIELD_PRODUCT_DETAILS_DESCRIPTION = 'sumedia_winestro_product_details.description';
    const FIELD_PRODUCT_DETAILS_SHOP_DESCRIPTION = 'sumedia_winestro_product_details.shop_description';
    const FIELD_PRODUCT_DETAILS_PRODUCT_NOTE = 'sumedia_winestro_product_details.product_note';
    const FIELD_PRODUCT_DETAILS_BUNDLE = 'sumedia_winestro_product_details.bundle';
    const FIELD_PRODUCT_DETAILS_STOCK_UPDATE_DATE = 'sumedia_winestro_product_details.stock_update_date';
    const FIELD_PRODUCT_DETAILS_WINESTRO_CONNECTION_ID = 'sumedia_winestro_product_details.winestro_connection_id';

    const FIELD_PRODUCT_SWITCHES_ACTIVESTATUS = 'sumedia_winestro_product_switches.activestatus';
    const FIELD_PRODUCT_SWITCHES_MANUFACTURER = 'sumedia_winestro_product_switches.manufacturer';
    const FIELD_PRODUCT_SWITCHES_FREE_SHIPPING = 'sumedia_winestro_product_switches.free_shipping';
    const FIELD_PRODUCT_SWITCHES_DESCRIPTION = 'sumedia_winestro_product_switches.description';

    const FIELD_ORDER_DETAILS_ORDER_NUMBER = 'sumedia_winestro_order_details.order_number';
    const FIELD_ORDER_DETAILS_EXPORT_TRIES = 'sumedia_winestro_order_details.export_tries';
    const FIELD_ORDER_DETAILS_BILLING_NUMBER = 'sumedia_winestro_order_details.billing_number';

    private array $customFieldSets = [
        'sumedia_winestro_product_details' => [
            'id' => '0190ec5441017d8a99f9a69ce5415d6b',
            'name' => 'sumedia_winestro_product_details',
            'config' => [
                'en-GB' => 'Winestro Product Details',
                'de-DE' => 'Winestro Produktdetails'
            ],
            'relations' => ['entityName' => 'product']
        ],
        'sumedia_winestro_product_switches' => [
            'id' => '0190ec5445e3780f8f3316c6ada07afa',
            'name' => 'sumedia_winestro_product_switches',
            'config' => [
                'en-GB' => 'Winestro Product Switches',
                'de-DE' => 'Winestro Produktschalter'
            ],
            'relations' => ['entityName' => 'product']
        ],
        'sumedia_winestro_order_details' => [
            'id' => '0190ec5447d87c8a9669e011376e79fb',
            'name' => 'sumedia_winestro_order_details',
            'config' => [
                'en-GB' => 'Winestro Order Details',
                'de-DE' => 'Winestro Bestelldetails'
            ],
            'relations' => ['entityName' => 'order']
        ]
    ];

    private array $customFields = [

        'sumedia_winestro_product_details.apnr' => [
            'id' => '0190ec544188752884442da7dd55823f',
            'customFieldSetId' => '0190ec5441017d8a99f9a69ce5415d6b',
            'name' => 'sumedia_winestro_product_details_apnr',
            'config' => [
                'en-GB' => 'APNR',
                'de-DE' => 'APNR'
            ],
            'type' => 'text',
            'customFielPosition' => 1,
            'allowCustomerWrite' => false,
            'allowCartExpose' => true
        ],
        'sumedia_winestro_product_details.article_number' => [
            'id' => '0190ec5441e672b2adfdbe91820c22da',
            'customFieldSetId' => '0190ec5441017d8a99f9a69ce5415d6b',
            'name' => 'sumedia_winestro_product_details_article_number',
            'config' => [
                'en-GB' => 'Article Number',
                'de-DE' => 'Artikelnummer'
            ],
            'type' => 'text',
            'customFieldPosition' => 2,
            'allowCustomerWrite' => false,
            'allowCartExpose' => true
        ],
        'sumedia_winestro_product_details.bottles' => [
            'id' => '0190ec5442467ce18782cd89dd5642e3',
            'customFieldSetId' => '0190ec5441017d8a99f9a69ce5415d6b',
            'name' => 'sumedia_winestro_product_details_bottles',
            'config' => [
                'en-GB' => 'Numbers of bottles',
                'de-DE' => 'Flaschenanzahl'
            ],
            'type' => 'number',
            'customFieldPosition' => 3,
            'allowCustomerWrite' => false,
            'allowCartExpose' => false
        ],
        'sumedia_winestro_product_details.best_before_date' => [
            'id' => '0190ec5442b070dc861af784594f333e',
            'customFieldSetId' => '0190ec5441017d8a99f9a69ce5415d6b',
            'name' => 'sumedia_winestro_product_details_best_before_date',
            'config' => [
                'en-GB' => 'Best Before Date',
                'de-DE' => 'Haltbarkeitsdatum'
            ],
            'type' => 'date',
            'customFieldPosition' => 4,
            'allowCustomerWrite' => false,
            'allowCartExpose' => false
        ],
        'sumedia_winestro_product_details.shelf_life' => [
            'id' => '0190ec54431872da968fd08b6d19e20e',
            'customFieldSetId' => '0190ec5441017d8a99f9a69ce5415d6b',
            'name' => 'sumedia_winestro_product_details_shelf_life',
            'config' => [
                'en-GB' => 'Shelf Life',
                'de-DE' => 'Lagerfähigkeit'
            ],
            'type' => 'text',
            'customFieldPosition' => 5,
            'allowCustomerWrite' => false,
            'allowCartExpose' => false
        ],
        'sumedia_winestro_product_details.e_label_free_text' => [
            'id' => '0190ec5443717bcab2c67ab24c3273e9',
            'customFieldSetId' => '0190ec5441017d8a99f9a69ce5415d6b',
            'name' => 'sumedia_winestro_product_details_e_label_free_text',
            'config' => [
                'en-GB' => 'E-Label Free Text',
                'de-DE' => 'E-Label Freitext',
                'componentName' => 'sw-text-editor',
                'customFieldType' => 'html'
            ],
            'type' => 'html',
            'customFielPosition' => 6,
            'allowCustomerWrite' => false,
            'allowCartExpose' => false
        ],
        'sumedia_winestro_product_details.description' => [
            'id' => '0190ec5443cd755dbe5fba09dd293761',
            'customFieldSetId' => '0190ec5441017d8a99f9a69ce5415d6b',
            'name' => 'sumedia_winestro_product_details_description',
            'config' => [
                'en-GB' => 'Productdescription',
                'de-DE' => 'Produktbeschreibung',
                'componentName' => 'sw-text-editor',
                'customFieldType' => 'html'
            ],
            'type' => 'html',
            'customFieldPosition' => 7,
            'allowCustomerWrite' => false,
            'allowCartExpose' => false
        ],
        'sumedia_winestro_product_details.shop_description' => [
            'id' => null,
            'customFieldSetId' => '0190ec5441017d8a99f9a69ce5415d6b',
            'name' => 'sumedia_winestro_product_details_shop_description',
            'config' => [
                'en-GB' => 'Shop Description',
                'de-DE' => 'Shopbeschreibung',
                'componentName' => 'sw-text-editor',
                'customFieldType' => 'html'
            ],
            'type' => 'html',
            'customFieldPosition' => 8,
            'allowCustomerWrite' => false,
            'allowCartExpose' => false
        ],
        'sumedia_winestro_product_details.product_note' => [
            'id' => '0190ec544426735b8e4ace351285e84d',
            'customFieldSetId' => '0190ec5441017d8a99f9a69ce5415d6b',
            'name' => 'sumedia_winestro_product_details_product_note',
            'config' => [
                'en-GB' => 'Producte Note',
                'de-DE' => 'Produktnotiz',
                'componentName' => 'sw-text-editor',
                'customFieldType' => 'html'
            ],
            'type' => 'html',
            'customFieldPosition' => 9,
            'allowCustomerWrite' => false,
            'allowCartExpose' => false
        ],
        'sumedia_winestro_product_details.bundle' => [
            'id' => '0190ec54447f7876904e1ddad1924f16',
            'customFieldSetId' => '0190ec5441017d8a99f9a69ce5415d6b',
            'name' => 'sumedia_winestro_product_details_bundle',
            'config' => [
                'en-GB' => 'Package Items',
                'de-DE' => 'Sammelpaket Artikel'
            ],
            'type' => 'text',
            'customFieldPosition' => 10,
            'allowCustomerWrite' => false,
            'allowCartExpose' => false
        ],
        'sumedia_winestro_product_details.stock_update_date' => [
            'id' => '0190ec5444d670959b723252bf4e2269',
            'customFieldSetId' => '0190ec5441017d8a99f9a69ce5415d6b',
            'name' => 'sumedia_winestro_product_details_stock_update_date',
            'config' => [
                'en-GB' => 'Last Stock Update',
                'de-DE' => 'Letzte Lagerbestandsaktualisierung'
            ],
            'type' => 'datetime',
            'customFieldPosition' => 11,
            'allowCustomerWrite' => true,
            'allowCartExpose' => false
        ],
        'sumedia_winestro_product_details.winestro_connection_id' => [
            'id' => '0190ec54452e72a1837ef9b7c88aaae8',
            'customFieldSetId' => '0190ec5441017d8a99f9a69ce5415d6b',
            'name' => 'sumedia_winestro_product_details_winestro_connection_id',
            'active' => false,
            'config' => [
                'en-GB' => 'Winestro Connection ID',
                'de-DE' => 'Winestro Connection ID'
            ],
            'type' => 'text',
            'customFieldPosition' => 12,
            'allowCustomerWrite' => false,
            'allowCartExpose' => false
        ],
        'sumedia_winestro_product_switches.activestatus' => [
            'id' => null,
            'customFieldSetId' => '0190ec5445e3780f8f3316c6ada07afa',
            'name' => 'sumedia_winestro_product_switches_activestatus',
            'config' => [
                'en-GB' => 'Update Activestatus',
                'de-DE' => 'Aktivstatus aktualisieren'
            ],
            'type' => 'switch',
            'customFieldPosition' => 1,
            'allowCustomerWrite' => true,
            'allowCartExpose' => false
        ],
        'sumedia_winestro_product_switches.manufacturer' => [
            'id' => '0190ec54458a7aac806ae24cd5597003',
            'customFieldSetId' => '0190ec5445e3780f8f3316c6ada07afa',
            'name' => 'sumedia_winestro_product_switches_manufacturer',
            'config' => [
                'en-GB' => 'Update Manufacturer',
                'de-DE' => 'Hersteller aktualisieren'
            ],
            'type' => 'switch',
            'allowCustomerWrite' => true,
            'allowCartExpose' => false
        ],
        'sumedia_winestro_product_switches.free_shipping' => [
            'id' => null,
            'customFieldSetId' => '0190ec5445e3780f8f3316c6ada07afa',
            'name' => 'sumedia_winestro_product_switches_free_shipping',
            'config' => [
                'en-GB' => 'Update Freeshipping',
                'de-DE' => 'Kostenlosen Versand aktualisieren'
            ],
            'type' => 'switch',
            'customFieldPosition' => 3,
            'allowCustomerWrite' => true,
            'allowCartExpose' => false
        ],
        'sumedia_winestro_product_switches.description' => [
            'id' => '0190ec54465d7ee3b616c46c40f98c48',
            'customFieldSetId' => '0190ec5445e3780f8f3316c6ada07afa',
            'name' => 'sumedia_winestro_product_switches_description',
            'config' => [
                'en-GB' => 'Update Description',
                'de-DE' => 'Beschreibung aktualisieren'
            ],
            'type' => 'switch',
            'customFieldPosition' => 4,
            'allowCustomerWrite' => true,
            'allowCartExpose' => false
        ],
        'sumedia_winestro_order_details.order_number' => [
            'id' => '0190ec5446bb785b906faaeaa56a124e',
            'customFieldSetId' => '0190ec5447d87c8a9669e011376e79fb',
            'name' => 'sumedia_winestro_order_details_order_number',
            'config' => [
                'en-GB' => 'Order Number',
                'de-DE' => 'Bestellnummer',
            ],
            'type' => 'text',
            'customFieldPosition' => 1,
            'allowCustomerWrite' => false,
            'allowCartExpose' => false
        ],
        'sumedia_winestro_order_details.export_tries' => [
            'id' => '0190ec5447137222902e908c50cbb33b',
            'customFieldSetId' => '0190ec5447d87c8a9669e011376e79fb',
            'name' => 'sumedia_winestro_order_details_export_tries',
            'config' => [
                'en-GB' => 'Export tries',
                'de-DE' => 'Exportversuche'
            ],
            'type' => 'number',
            'customFieldPosition' => 2,
            'allowCustomerWrite' => true,
            'allowCartExpose' => false
        ],
        'sumedia_winestro_order_details.billing_number' => [
            'id' => '0190ec5447787af6a9271fba02a4162c',
            'customFieldSetId' => '0190ec5447d87c8a9669e011376e79fb',
            'name' => 'sumedia_winestro_order_details_billing_number',
            'config' => [
                'en-GB' => 'Billing Number',
                'de-DE' => 'Rechnungsnummer'
            ],
            'type' => 'text',
            'customFieldPosition' => 3,
            'allowCustomerWrite' => false,
            'allowCartExpose' => false
        ]
    ];

    public function getConstants(): array
    {
        $ref = new \ReflectionClass(self::class);
        return (array) $ref->getConstants();
    }

    public function mapKey(string $key): mixed
    {
        if (false !== strpos($key, '.')) {
            if (isset($this->customFields[$key])) {
                return $this->customFields[$key];
            }
        } else {
            if (isset($this->customFieldSets[$key])) {
                return $this->customFieldSets[$key];
            }
        }
        return null;
    }

    public function toArray(): array
    {
        return [
            'customFieldSets' => $this->customFieldSets,
            'customFields' => $this->customFields
        ];
    }
}