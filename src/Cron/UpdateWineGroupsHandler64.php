<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Cron;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;
use Sumedia\WinestroApi\Service\Wbo\Command\SetWineGroups;

class UpdateWineGroupsHandler64 extends AbstractCronHandler
{
    protected SetWineGroups $command;

    public function __construct(LoggerInterface $logger, EntityRepository $scheduledTaskRepository, Container $container)
    {
        parent::__construct($logger, $scheduledTaskRepository);
        $this->command = $container->get(SetWineGroups::class);
    }

    public static function getHandledMessages(): iterable
    {
        return [ UpdateWineGroups::class ];
    }

    public function run(): void
    {
        $this->command->execute();
    }
}
