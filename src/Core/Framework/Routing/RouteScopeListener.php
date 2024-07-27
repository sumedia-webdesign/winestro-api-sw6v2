<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Core\Framework\Routing;

use Shopware\Core\Framework\Routing\Exception\InvalidRouteScopeException;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class RouteScopeListener extends \Shopware\Core\Framework\Routing\RouteScopeListener
{
    public function checkScope(ControllerEvent $event): void
    {
        try {
            parent::checkScope($event);
        } catch(InvalidRouteScopeException $e) {
            $route = (string) $event->getRequest()->get('_route');
            if (!in_array($route, [
                'sumedia_wbo_check_connection'
            ])) {
                throw $e;
            }
        }
    }
}
