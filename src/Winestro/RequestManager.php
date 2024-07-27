<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro;

use Sumedia\WinestroApi\Winestro\Request\RequestInterface;
use Symfony\Component\DependencyInjection\Container;

class RequestManager implements RequestManagerInterface
{
    const GET_ARTICLES_FROM_WINESTRO_REQUEST = 'GetArticlesFromWinestroRequest';
    const GET_STOCK_FROM_WINESTRO_REQUEST = 'GetStockFromWinestroRequest';
    const SEND_ORDER_TO_WINESTRO_REQUEST = 'SendOrderToWinestroRequest';
    const GET_ORDER_STATUS_FROM_WINESTRO_REQUEST = 'GetOrderStatusFromWinestroRequest';
    const GET_CUSTOMER_GROUPS_FROM_WINESTRO_REQUEST = 'GetCustomerGroupsFromWinestroRequest';
    const GET_CUSTOMERS_FROM_WINESTRO_REQUEST = 'GetCustomersFromWinestroRequest';

    const GET_ARTICLES_FROM_WINESTRO_RESPONSE = 'GetArticlesFromWinestroResponse';
    const GET_STOCK_FROM_WINESTRO_RESPONSE = 'GetStockFromWinestroResponse';
    const SEND_ORDER_TO_WINESTRO_RESPONSE = 'SendOrderToWinestroResponse';
    const GET_ORDER_STATUS_FROM_WINESTRO_RESPONSE = 'GetOrderStatusFromWinestroResponse';
    const GET_CUSTOMER_GROUPS_FROM_WINESTRO_RESPONSE = 'GetCustomerGroupsFromWinestroResponse';
    const GET_CUSTOMERS_FROM_WINESTRO_RESPONSE = 'GetCustomersFromWinestroResponse';

    public function __construct(private Container $container){}

    public function createRequest(string $requestName): RequestInterface
    {
        $class = 'Sumedia\\WinestroApi\\Winestro\\Request\\' . $requestName;
        if (!class_exists($class)) {
            throw new \RuntimeException(sprintf('Class "%s" does not exist.', $class));
        }

        return $this->container->get($class);
    }
}