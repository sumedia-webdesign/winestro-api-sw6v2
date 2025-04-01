<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Sumedia\WinestroApi\Winestro\DataMapper\CustomFieldsMapper;

/**
 * @internal
 */
class Migration1743470037CustomFieldsUpdate extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1743470037;
    }

    public function update(Connection $connection): void
    {
        $customFieldsMapper = new CustomFieldsMapper();
        $data = [
            'eLabelLink' => $customFieldsMapper->mapKey('sumedia_winestro_product_details.e_label_link'),
            'eLabelExternalLink' => $customFieldsMapper->mapKey('sumedia_winestro_product_details.e_label_external_link'),
        ];

        $query = $connection->executeQuery("SELECT HEX(`id`) as `id` FROM `custom_field_set` WHERE `name` = 'sumedia_winestro_product_details'");
        if (!$query->rowCount()) {
            return;
        }
        $customFieldSetId = $query->fetchAssociative()['id'];

        foreach ($data as $field) {
            $name = str_replace('.', '_', $field['name']);
            $connection->executeStatement("INSERT INTO `custom_field` 
                VALUES (UNHEX(?), ?, ?, ?, ?, UNHEX(?), ?, ?, ?, ?, ?)",[
                $field['id'],
                $name,
                $field['type'],
                json_encode([
                    'label' => [
                        'de-DE' => $field['config']['de-DE'],
                        'en-GB' => $field['config']['en-GB']
                    ]
                ]),
                1,
                $customFieldSetId,
                date('Y-m-d H:i:s'),
                null,
                $field['allowCustomerWrite'] ? 1 : 0,
                $field['allowCartExpose'] ? 1 : 0,
                1
            ]);
        }
    }
}
