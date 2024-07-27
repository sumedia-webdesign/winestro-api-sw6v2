<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\WinestroApi;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SumediaWinestroApi extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
/*
        $locator = new FileLocator($this->getPath() . '/Resources/config/services');
        $loader = new XmlFileLoader($container, $locator);
        if (version_compare(Kernel::SHOPWARE_FALLBACK_VERSION, '6.5', '<')) {
            $loader->load('6.4.xml');
        } elseif (version_compare(Kernel::SHOPWARE_FALLBACK_VERSION, '6.6', '<')) {
            $loader->load('6.5.xml');
        } else {
            $loader->load('6.6.xml');
        }
*/
    }
}
