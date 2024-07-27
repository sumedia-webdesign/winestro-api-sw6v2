<?php
namespace Sumedia\WinestroApi\Winestro\Subscriber;

use Shopware\Core\Checkout\Order\Event\OrderStateMachineStateChangeEvent;
use Sumedia\WinestroApi\Winestro\TaskManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DisableEmailsSubscriber implements EventSubscriberInterface
{
    public function __construct(private TaskManagerInterface $taskManager){}

    public static function getSubscribedEvents(): array
    {
        return [
            'state_enter.order_transaction.state.paid' => 'removeEmailEvent',
            'state_enter.order.state.in_progress' => 'removeEmailEvent',
            'state_enter.order.state.completed' => 'removeEmailEvent'
        ];
    }

    public function removeEmailEvent(OrderStateMachineStateChangeEvent $event): void
    {
        if ($this->taskManager->getParameter('suppressEmail')) {
            $event->stopPropagation();
        }
    }
}