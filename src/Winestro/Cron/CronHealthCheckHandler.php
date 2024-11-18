<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskDefinition;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskEntity;
use Sumedia\WinestroApi\ConfigInterface;
use Sumedia\WinestroApi\Winestro\CronManagerInterface;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;
use Sumedia\WinestroApi\Winestro\TaskManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: CronHealthCheck::class)]
class CronHealthCheckHandler extends AbstractCron
{
    public function __construct(
        protected EntityRepository $scheduledTaskRepository,
        protected LogManagerInterface $logManager,
        protected CronManagerInterface $cronManager,
        protected TaskManagerInterface $taskManager,
        protected ConfigInterface $config,
        protected Context $context
    ){
        parent::__construct($scheduledTaskRepository, $logManager, $cronManager, $taskManager, $config);
    }

    public static function getHandledMessages(): iterable
    {
        return [ CronHealthCheck::class ];
    }

    public function run(): void
    {
        $this->logManager->newLogId();
        try {
            $this->logManager->logProcess("[cron health check]");
            $tasks = $this->getScheduledTasks();
            $this->checkHealth($tasks);
            $this->logManager->logProcess('[cron success]');
        } catch (\Exception $e) {
            $this->logManager->logException($e);
            $this->logManager->logProcess('[cron message] ' .
                $e->getMessage() . ' in ' . $e->getFile() . ' on ' . $e->getLine() . ': ' . $e->getTraceAsString()
            );
            $this->logManager->logProcess('[cron failed]');
        } finally {
            $this->logManager->resetLogId();
        }
    }

    private function getScheduledTasks() : EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('name', [
            'sumedia_winestro.execute_cron',
            'sumedia_winestro.cron_5_minutes',
            'sumedia_winestro.cron_15_minutes',
            'sumedia_winestro.cron_30_minutes',
            'sumedia_winestro.cron_1_hour',
            'sumedia_winestro.cron_6_hour',
            'sumedia_winestro.cron_12_hour',
            'sumedia_winestro.cron_1_day',
            'sumedia_winestro.cron_1_week',
            'sumedia_winestro.cron_1_month'
        ]));
        return $this->scheduledTaskRepository->search($criteria, $this->context);
    }

    private function checkHealth(EntitySearchResult $tasks): void
    {
        /** @var ScheduledTaskEntity $task */
        foreach ($tasks as $task) {
            if ($task->getStatus() === ScheduledTaskDefinition::STATUS_FAILED) {
                $this->scheduledTaskRepository->update([[
                    'id' => $task->getId(),
                    'status' => ScheduledTaskDefinition::STATUS_SCHEDULED
                ]], $this->context);
            }
        }
    }
}
