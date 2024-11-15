<?php declare(strict_types=1);

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\WinestroApi\Winestro\DataMapper;

class TaskMapper implements DataMapperInterface
{
    const PRODUCT_IMPORT_TASK = 'productImportTask';
    const PRODUCT_IMAGE_UPDATE_TASK = 'productImageUpdateTask';
    const PRODUCT_STOCK_TASK = 'productStockTask';
    const PRODUCT_CATEGORY_ASSIGNMENT_TASK = 'productCategoryAssignmentTask';
    const ORDER_EXPORT_TASK = 'orderExportTask';
    const ORDER_STATUS_UPDATE_TASK = 'orderStatusUpdateTask';
    const NEWSLETTER_RECEIVER_IMPORT_TASK = 'newsletterImportTask';

    const PRODUCT_STOCK_ADDER_EXTENSION = 'productStockAdderExtension';

    private array $tasks = [
        'productImportTask' => [
            'id' => null,
            'type' => 'productImport',
            'name' => null,
            'winestroConnectionId' => null,
            'articleNumberFormat' => '[articlenumber+year+bottling]',
            'articleNumberYearSeparator' => '+',
            'articleNumberBottlingSeparator' => '+',
            'defaultManufacturer' => null,
            'tax' => null,
            'reducedTax' => null,
            'deliveryTime' => null,
            'visibleInSalesChannelsIds' => [],
            'enabled' => [
                'enabled' => true,
                'activestatus' => true,
                'description' => true,
                'freeshipping' => true,
                'manufacturer' => true
            ],
            'extensions' => [],
            'execute' => []
        ],
        'productImageUpdateTask' => [
            'id' => null,
            'type' => 'productImageUpdate',
            'name' => null,
            'winestroConnectionId' => null,
            'maxImageWidth' => 860,
            'maxImageHeight' => 860,
            'mediaFolder' => null,
            'enabled' => [
                'enabled' =>  true
            ],
            'extensions' => [],
            'execute' => []
        ],
        'productStockTask' => [
            'id' => null,
            'type' => 'productStock',
            'name' => null,
            'winestroConnectionId' => null,
            'sellingLimit' => 0,
            'enabled' => [
                'enabled' => true
            ],
            'extensions' => [],
            'execute' => []
        ],
        'productCategoryAssignmentTask' => [
            'id' => null,
            'type' => 'productCategoryAssignment',
            'name' => null,
            'winestroConnectionId' => null,
            'salesChannelId' => null,
            'categoryIdentifier' => 'Winestro',
            'enabled' => [
                'enabled' => true
            ],
            'extensions' => [],
            'execute' => []
        ],
        'orderExportTask' => [
            'id' => null,
            'type' => 'orderExport',
            'name' => null,
            'winestroConnectionId' => null,
            'productsFromWinestroConnectionIds' => null,
            'productsFromSalesChannelsIds' => null,
            'enabled' => [
                'enabled' => true,
                'sendWinestroEmail' => false
            ],
            'extensions' => [],
            'execute' => []
        ],
        'orderStatusUpdateTask' => [
            'id' => null,
            'type' => 'orderStatusUpdate',
            'name' => null,
            'winestroConnectionId' => null,
            'suppressEmail' => true,
            'enabled' => [
                'enabled' => true
            ],
            'extensions' => [],
            'execute' => []
        ],
        'newsletterReceiverImportTask' => [
            'id' => null,
            'type' => 'newsletterReceiverImport',
            'name' => null,
            'winestroConnectionId' => null,
            'salesChannelId' => null,
            'enabled' => [
                'enabled' => true,
            ],
            'extensions' => [],
            'execute' => []
        ]
    ];

    private array $extensions = [
        'productStockAdderExtension' => [
            'id' => null,
            'type' => 'productStockAdder',
            'name' => null,
            'taskId' => null,
            'winestroConnectionId' => null,
            'enabled' => [
                'enabled' => true
            ]
        ]
    ];

    public function getConstants(): array
    {
        $ref = new \ReflectionClass(self::class);
        return (array) $ref->getConstants();
    }

    public function mapKey(string $key): mixed
    {
        if (str_ends_with($key, '_TASK') && isset($this->tasks[$key])) {
            return $this->tasks[$key];
        } elseif (isset($this->extensions[$key])) {
            return $this->extensions[$key];
        }
        return null;
    }

    public function toArray(): array
    {
        return [
            'tasks' => $this->tasks,
            'extensions' => $this->extensions
        ];
    }

}