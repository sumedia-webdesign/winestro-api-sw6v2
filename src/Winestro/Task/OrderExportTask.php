<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Task;

use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Sumedia\WinestroApi\Config;
use Sumedia\WinestroApi\RepositoryManager;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;
use Sumedia\WinestroApi\Winestro\RequestManager;
use Sumedia\WinestroApi\Winestro\Response\InvalidOrderExportException;
use Sumedia\WinestroApi\Winestro\Task\OrderExport\OrderExportBuilder;
use Sumedia\WinestroApi\Winestro\TaskManager;
use Symfony\Component\DependencyInjection\Container;

class OrderExportTask extends AbstractTask
{
    public function __construct(
        Container $container,
        Config $config,
        TaskManager $taskManager,
        RepositoryManager $repositoryManager,
        RequestManager $requestManager,
        LogManagerInterface $logManager,
        private OrderExportBuilder $orderExportBuilder
    ) {
        parent::__construct($container, $config, $taskManager, $repositoryManager, $requestManager, $logManager);
    }

    public function execute(TaskInterface $parentTask = null): void
    {
        $this->_execute($parentTask, [$this, 'orderExport']);
    }

    public function orderExport(): void
    {
        $salesChannels = $this->config->get('salesChannels');
        $connectionIds = [];
        $salesChannelsIds = [];
        foreach ($this['productsFromSalesChannelsIds'] as $salesChannelId) {
            if (isset($salesChannels[$salesChannelId])) {
                foreach ($this['productsFromWinestroConnectionIds'] as $connectionId) {
                    if (
                        isset($salesChannels[$salesChannelId]['winestroConnections'][$connectionId]['paymentMapping']) &&
                        isset($salesChannels[$salesChannelId]['winestroConnections'][$connectionId]['shippingMapping'])
                    ) {
                        if (!in_array($salesChannelId, $salesChannelsIds)) {
                            $salesChannelsIds[] = $salesChannelId;
                        }
                        if (!in_array($connectionId, $connectionIds)) {
                            $connectionIds[] = $connectionId;
                        }
                    }
                }
            }
        }

        if (!count($connectionIds) || !count($salesChannelsIds)) {
            $this->logManager->debug('there is no valid config for order export "' . $this['name'] . '" "' . $this['id'] . '"');
            $this->logManager->logProcess('[task invalid]');
            return;
        }

        $ordersCollection = $this->getOrdersCollection($salesChannelsIds);

        $orders = $this->orderExportBuilder->build($this, $ordersCollection);

        foreach ($orders as $buildedOrder) {
            $orderData = $buildedOrder['orderData'];
            $orderItem = $buildedOrder['order'];

            $date = $this->getLoginTriesDate($orderItem);
            $this->incrementLoginTries($orderItem, $date);

            if ($orderItem->getCreatedAt()->format('U') < $date->format('U')) {

                $connection = $this->getWinestroConnection();
                $request = $this->requestManager->createRequest(RequestManager::SEND_ORDER_TO_WINESTRO_REQUEST);
                foreach ($orderData as $key => $value) {
                    $request->setParameter($key, $value);
                }
                try {
                    $response = $connection->executeRequest($request);
                } catch (InvalidOrderExportException $exception) {
                    $this->logManager->logException($exception);
                    throw $exception;
                }

                $orderNumber = $response->toArray()['orderNumber'];

                if ($orderNumber !== null) {
                    $this->repositoryManager->update('order', [[
                        'id' => $orderItem->getId(),
                        'customFields' => [
                            'sumedia_winestro_order_details_order_number' => $orderNumber
                        ]
                    ]]);
                }
            }
        }
    }

    protected function getPluginInstallationDate(): \DateTimeImmutable
    {
        $plugin = $this->repositoryManager->search('plugin',
            (new Criteria())->addFilter(new EqualsFilter('name', 'SumediaWinestroApi'))
        )->first();
        return $plugin->getInstalledAt();
    }

    private function getOrdersCollection(array $salesChannelsIds): OrderCollection
    {
        return new OrderCollection($this->repositoryManager->search('order',
            (new Criteria())
                ->addAssociation('addresses')
                ->addAssociation('deliveries')
                ->addAssociation('lineItems')
                ->addAssociation('lineItems.product')
                ->addAssociation('transactions')
                ->addFilter(new EqualsFilter('customFields.sumedia_winestro_order_details_order_number', null ))
                ->addFilter(new EqualsAnyFilter('salesChannelId', $salesChannelsIds))
                ->addFilter(new MultiFilter('and', [
                    new RangeFilter('createdAt', [
                        RangeFilter::GTE => $this->getPluginInstallationDate()->format('Y-m-d H:i:s')
                    ]),
                    new RangeFilter('createdAt', [
                        RangeFilter::GTE =>
                            (new \DateTime())->sub(\DateInterval::createFromDateString('5 days'))
                            ->format('Y-m-d H:i:s')
                    ])
                ]))
                ->addFilter(new MultiFilter('or', [
                    new RangeFilter('customFields.sumedia_winestro_order_details_export_tries', [RangeFilter::LT => 4]),
                    new EqualsFilter('customFields.sumedia_winestro_order_details_export_tries', null)
                ]))
        )->getElements());
    }

    private function getLoginTriesDate(OrderEntity $orderItem): \DateTime
    {
        $tries = $orderItem->getCustomFieldsValue('sumedia_winestro_order_details_export_tries') ?? 0;
        if ($tries === 0) {
            return new \DateTime();
        }
        return (new \DateTime())->sub(\DateInterval::createFromDateString(
            (6 * $tries) . ' hours')
        );
    }

    private function incrementLoginTries(OrderEntity $orderItem, \DateTime $date)
    {
        if ($orderItem->getCreatedAt()->format('U') < $date->format('U')) {
            $tries = $orderItem->getCustomFieldsValue('sumedia_winestro_order_details_export_tries') ?? 0;
            $this->repositoryManager->update('order', [[
                'id' => $orderItem->getId(),
                'customFields' => [
                    'sumedia_winestro_order_details_export_tries' => $tries + 1
                ]
            ]]);
        }
    }
}