<?php declare(strict_types=1);

namespace Sumedia\WinestroAPI\Cron;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Psr\Log\LoggerInterface;
use Sumedia\WinestroAPI\Service\Wbo\Command\SetArticles;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: UpdateArticles::class)]
class UpdateArticlesHandler extends AbstractCronHandler
{
    protected SetArticles $command;

    public function __construct(LoggerInterface $logger, EntityRepository $scheduledTaskRepository, Container $container)
    {
        parent::__construct($logger, $scheduledTaskRepository);
        $this->command = $container->get(SetArticles::class);
    }

    public static function getHandledMessages(): iterable
    {
        return [ UpdateArticles::class ];
    }

    public function run(): void
    {
        $this->command->execute();
    }
}
