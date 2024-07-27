<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Task\Extension;

use Sumedia\WinestroApi\ConfigInterface;
use Sumedia\WinestroApi\Winestro\RequestManager;
use Sumedia\WinestroApi\Winestro\Response\NoEntriesException;
use Sumedia\WinestroApi\Winestro\Task\TaskInterface;
use Symfony\Component\DependencyInjection\Container;

class ProductStockAdderExtension extends AbstractExtension
{
    public function __construct(
        Container $container,
        ConfigInterface $config,
        private RequestManager $requestManager
    ){
        parent::__construct($container, $config);
    }

    public function execute(TaskInterface $task): void
    {
        $stock = $task->getParameter('stock');
        $articleNumber = $task->getParameter('articleNumber');
        if ($task['winestroConnectionId'] === $this['winestroConnectionId'] || null === $stock || null === $articleNumber) {
            return;
        }

        $connection = $this->getWinestroConnection();
        $request = $this->requestManager->createRequest(RequestManager::GET_STOCK_FROM_WINESTRO_REQUEST);
        $request->setParameter('artikelnr', $articleNumber);
        try {
            $response = $connection->executeRequest($request);
            $stock += (int) current($response->toArray());
            $task->setParameter('stock', $stock);
        } catch (NoEntriesException $e) {}
    }
}