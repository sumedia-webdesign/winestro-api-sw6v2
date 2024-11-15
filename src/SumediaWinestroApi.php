<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\WinestroApi;

use Shopware\Core\Framework\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SumediaWinestroApi extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }
}
