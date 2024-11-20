<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('core')]
class Migration1732132823InstallCategoryIdentifier extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1732132823;
    }

    public function update(Connection $connection): void
    {
        $setId = Uuid::randomHex();
        $setConfig = json_encode([
            'label' => [
                'de-DE' => 'Winestro Kategoriedetails',
                'en-GB' => 'Winestro Categorydetails'
            ]
        ]);
        $connection->executeStatement("
            INSERT IGNORE INTO custom_field_set (id, name, config, active, position, created_at)
            VALUES(UNHEX('" . $setId . "'), 'sumedia_winestro_category_details', '" . $setConfig . "', 1, 1, '" . date('Y-m-d H:i:s') . "')
        ");

        $connection->executeStatement("
            INSERT IGNORE INTO custom_field_set_relation (id, set_id, entity_name, created_at)
            VALUES(UNHEX('" . Uuid::randomHex() . "'), UNHEX('" . $setId . "'), 'category', '" . date('Y-m-d H:i:s') . "')
        ");

        $config = json_encode([
            "label" => [
                "de-DE" => "Winestro Kategorie aus Warengruppe",
                "en-GB" => "Winestro categorie from waregroups"
            ]
        ]);
        $connection->executeStatement("
            INSERT IGNORE INTO custom_field(id, name, type, config, active, set_id, created_at)
            VALUES('UNHEX(" . Uuid::randomHex() . ")', 'sumedia_winestro_category_details_category_identifier', 'text', '" . $config . "', 1, UNHEX('" . $setId . "'), '" . date('Y-m-d H:i:s') . "')
        ");

    }
}
