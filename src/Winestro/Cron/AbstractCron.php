<?php declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cron;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Sumedia\WinestroApi\ConfigInterface;
use Sumedia\WinestroApi\Winestro\CronManagerInterface;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;
use Sumedia\WinestroApi\Winestro\TaskManagerInterface;

abstract class AbstractCron extends ScheduledTaskHandler
{
    private array $crons = [];

    public function __construct(
        protected EntityRepository $scheduledTaskRepository,
        protected LogManagerInterface $logManager,
        protected CronManagerInterface $cronManager,
        protected TaskManagerInterface $taskManager,
        protected ConfigInterface $config
    ){
        parent::__construct($scheduledTaskRepository, $logManager->getErrorLogger());
        $this->crons = $this->config->get('cron') ?? [];
    }

    public function _run(string $time) {
        try {
            $taskIds = $this->cronManager->getTaskIdsByTime($time);
            if (!count($taskIds)) {
                return;
            }
            if (!is_array($this->crons)) {
                return;
            }
            $cronId = 0;
            foreach ($this->crons as $cron) {
                if ($cron['times'] === $time) {
                    $cronId = $cron['id'];
                }
            }
            $this->logManager->setTaskId(uniqid());
            $this->logManager->logCron("[cron run $cronId]");

            foreach ($taskIds as $taskId) {
                $this->logManager->logCron('[cron task ' . $taskId . ']');
                $this->taskManager->getTask($taskId)->execute();
            }
            $this->logManager->logCron('[cron success]');
        } catch (\Exception $e) {
            $this->logManager->logException($e);
            $this->logManager->logCron('[cron message] ' .
                $e->getMessage() . ' in ' . $e->getFile() . ' on ' . $e->getLine() . ': ' . $e->getTraceAsString()
            );
            $this->logManager->logCron('[cron failed]');
        }
    }
}