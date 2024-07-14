<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\WinestroAPI\Setup;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Sumedia\WinestroAPI\Service\Wbo\Command\SetAbroadDiscounts;
use Sumedia\WinestroAPI\Service\Wbo\Command\SetDiscounts;
use Sumedia\WinestroAPI\Service\Wbo\Command\SetInlandDiscounts;
use Sumedia\WinestroAPI\Setting\Service\SettingsService;

class Uninstall extends SetupAbstract
{
    private Context $context;

    public function uninstall(Context $context): void
    {
        $this->context = $context;
        $this->removeConfiguration();
        $this->drop();
    }

    protected function removeConfiguration(): void
    {
        $criteria = (new Criteria())
            ->addFilter(new ContainsFilter('configurationKey', SettingsService::SYSTEM_CONFIG_DOMAIN . '.'));
        $idSearchResult = $this->systemConfigRepository->searchIds($criteria, $this->context);

        $ids = \array_map(static function ($id) {
            return ['id' => $id];
        }, $idSearchResult->getIds());

        $this->systemConfigRepository->delete($ids, $this->context);
    }

    protected function drop()
    {
        $time = time();
        $this->connection->executeStatement("
            START TRANSACTION;
            
            DROP TABLE IF EXISTS `wbo_products`;
            DROP TABLE IF EXISTS `wbo_articles`;
            DROP TABLE IF EXISTS `wbo_wine_groups`;
            
            COMMIT;
        ");
    }
}
