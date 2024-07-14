<?php declare(strict_types=1);

namespace Sumedia\WinestroAPI\Cron;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Sumedia\WinestroAPI\Service\Wbo\Command\CheckOrderStatus as CheckOrderStatusCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: CheckOrderStatus::class)]
class CheckOrderStatusHandler extends AbstractCronHandler
{
    protected CheckOrderStatusCommand $command;

    public function __construct(LoggerInterface $logger, EntityRepository $scheduledTaskRepository, Container $container)
    {
        parent::__construct($logger, $scheduledTaskRepository);
        $this->command = $container->get(CheckOrderStatusCommand::class);
    }

    public static function getHandledMessages(): iterable
    {
        return [ CheckOrderStatus::class ];
    }

    public function run(): void
    {
        $this->command->execute();
    }
}
