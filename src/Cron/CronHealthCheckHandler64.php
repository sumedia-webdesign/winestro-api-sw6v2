<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Cron;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Psr\Log\LoggerInterface;
use Sumedia\WinestroApi\Service\Wbo\Command\CronHealthCheck as CronHealthCheckCommand;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class CronHealthCheckHandler64 extends AbstractCronHandler
{
    protected CronHealthCheckCommand $command;

    public function __construct(LoggerInterface $logger, EntityRepository $scheduledTaskRepository, Container $container)
    {
        parent::__construct($logger, $scheduledTaskRepository);
        $this->command = $container->get(CronHealthCheckCommand::class);
    }

    public static function getHandledMessages(): iterable
    {
        return [ CronHealthCheck::class ];
    }

    public function run(): void
    {
        $this->command->execute();
    }
}
