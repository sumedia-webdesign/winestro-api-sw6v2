<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Task;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\StateMachine\Transition;
use Sumedia\WinestroApi\Config;
use Sumedia\WinestroApi\RepositoryManager;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;
use Sumedia\WinestroApi\Winestro\RequestManager;
use Sumedia\WinestroApi\Winestro\Response\NoEntriesException;
use Sumedia\WinestroApi\Winestro\TaskManager;
use Symfony\Component\DependencyInjection\Container;

class OrderStatusUpdateTask extends AbstractTask
{
    public function __construct(
        Container $container,
        Config $config,
        TaskManager $taskManager,
        RepositoryManager $repositoryManager,
        RequestManager $requestManager,
        LogManagerInterface $logManager,
        private StateMachineRegistry $stateMachineRegistry,
        private Context $context
    ) {
        parent::__construct($container, $config, $taskManager, $repositoryManager, $requestManager, $logManager);
    }

    public function execute(TaskInterface $parentTask = null): void
    {
        $this->_execute($parentTask, [$this, 'orderStatusUpdate']);
    }

    public function orderStatusUpdate(): void
    {
        $orderState = $this->repositoryManager->search('state_machine',
            (new Criteria())->addFilter(new EqualsFilter('technicalName', 'order.state'))
        )->first();

        $stateIds = $this->repositoryManager->searchIds('state_machine_state',
            (new Criteria())
                ->addFilter(new EqualsAnyFilter('technicalName', ['open', 'in_progress']))
                ->addFilter(new EqualsFilter('stateMachineId', $orderState->getId()))
        )->getIds();

        $orders = $this->repositoryManager->search('order',
            (new Criteria())
                ->addAssociation('transactions')
                ->addFilter(new NotFilter('or', [
                    new EqualsFilter('customFields.sumedia_winestro_order_details_order_number', null),
                    new EqualsFilter('customFields.sumedia_winestro_order_details_order_number', '')
                ]))
                ->addFilter(new EqualsAnyFilter('stateId', $stateIds))
                ->addFilter(new RangeFilter('createdAt', [
                    RangeFilter::GT => ((new \DateTime)->sub(\DateInterval::createFromDateString('1 month')))->format('Y-m-d H:i:s')
                ]))
        );

        foreach ($orders as $order) {
            $orderNumber = $order->getCustomFieldsValue('sumedia_winestro_order_details_order_number');

            $connection = $this->getWinestroConnection();
            $request = $this->requestManager->createRequest(RequestManager::GET_ORDER_STATUS_FROM_WINESTRO_REQUEST);
            $request->setParameter('auftragnummer', $orderNumber);
            try {
                $response = $connection->executeRequest($request);

                $status = $response->toArray();

                if ($this['suppressEmail']) {
                    $this->taskManager->setParameter('suppressEmail', true);
                }

                switch ($status['payedStatus']) {
                    case 'bezahlt':
                        foreach ($order->getTransactions() as $transaction) {
                            $this->stateMachineRegistry->transition(new Transition(
                                OrderTransactionDefinition::ENTITY_NAME,
                                $transaction->getId(),
                                'paid',
                                'stateId'
                            ), $this->context);
                        }
                        break;

                }

                switch ($status['status']) {
                    case 'erledigt':
                        try {
                            $this->stateMachineRegistry->transition(new Transition(
                                OrderDefinition::ENTITY_NAME,
                                $order->getId(),
                                'complete',
                                'stateId'
                            ), $this->context);
                        } catch (\Exception $e) {
                            $this->stateMachineRegistry->transition(new Transition(
                                OrderDefinition::ENTITY_NAME,
                                $order->getId(),
                                'process',
                                'stateId'
                            ), $this->context);

                            $this->stateMachineRegistry->transition(new Transition(
                                OrderDefinition::ENTITY_NAME,
                                $order->getId(),
                                'complete',
                                'stateId'
                            ), $this->context);
                        }
                        break;
                    case 'bearbeitung':
                        $this->stateMachineRegistry->transition(new Transition(
                            OrderDefinition::ENTITY_NAME,
                            $order->getId(),
                            'process',
                            'stateId'
                        ), $this->context);
                        break;
                }

                $this->taskManager->removeParameter('suppressEmail');

                if (is_string($status['link']) && preg_match('#(\d{9,})#', $status['link'], $matches)) {
                    $trackingCodes = $order->getTrackingCodes();
                    if (!in_array($matches[1], $trackingCodes)) {
                        $trackingCodes[] = $matches[1];

                        $this->repositoryManager->update('order', [[
                            'id' => $order->getId(),
                            'trackingCodes' => $trackingCodes
                        ]]);
                    }
                }

                if (is_string($status['billingNumber']) && !empty($status['billingNumber'])) {
                    $this->repositoryManager->update('order', [[
                        'id' => $order->getId(),
                        'customFields' => [
                            'sumedia_winestro_order_details_billing_number' => $status['billingNumber']
                        ]
                    ]]);
                }

            } catch (NoEntriesException $e) {}
        }
    }
}