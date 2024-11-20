<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\WinestroApi;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\PrefixFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SumediaWinestroApi extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        $container = $this->container;

        if (!$uninstallContext->keepUserData()) {

            $configPrefix = 'SumediaWinestroApi.config.';
            $systemConfigService = $container->get(SystemConfigService::class);
            $context = DefaultContext::createCLIContext();

            $repository = $this->container->get('system_config.repository');
            $criteria = new Criteria();
            $criteria->addFilter(new PrefixFilter('configurationKey', $configPrefix));

            $configs = $repository->search($criteria, $context);

            foreach ($configs as $config) {
                $systemConfigService->delete($config->get('configurationKey'));
            }
        }
    }
}
