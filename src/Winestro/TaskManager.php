<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro;

use Sumedia\WinestroApi\ConfigInterface;
use Sumedia\WinestroApi\Winestro\Task\TaskInterface;
use Symfony\Component\DependencyInjection\Container;

class TaskManager implements TaskManagerInterface
{
    const PRODUCT_IMPORT_TASK = 'productImport';
    const PRODUCT_IMAGE_UPDATE_TASK = 'productImageUpdate';
    const PRODUCT_STOCK_TASK = 'productStock';
    const PRODUCT_CATEGORY_ASSIGNMENT_TASK = 'productCategoryAssignment';
    const ORDER_EXPORT_TASK = 'orderExport';
    const ORDER_STATUS_UPDATE_TASK = 'orderStatusUpdate';
    const NEWSLETTER_RECEIVER_IMPORT_TASK = 'newsletterReceiverImport';
    const PRODUCT_STOCK_ADDER_EXTENSION = 'productStockAdder';

    private array $taskConfig = [];
    private array $parameters = [];
    private array $tasks = [];

    public function __construct(
        private Container $container,
        private ConfigInterface $config,
        private LogManagerInterface $logManager
    ){
        $this->taskConfig = $this->config->get('tasks') ?? [];
    }

    public function getTaskConfig(): array
    {
        return $this->taskConfig;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function hasParameter(string $key): bool
    {
        return isset($this->parameters[$key]);
    }

    public function getParameter(string $key): mixed
    {
        if ($this->hasParameter($key)) {
            return $this->parameters[$key];
        }
        return null;
    }

    public function setParameter(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    public function removeParameter(string $key): void
    {
        if (isset($this->parameters[$key])) {
            unset($this->parameters[$key]);
        }
    }

    public function lockTask(string $taskId): void
    {
        if ($this->isLockedTask($taskId)) {
            $this->logManager->logProcess('[task locked]');
            throw new Exception\TaskLockedException('task locked');
        }

        $lockedTasks = $this->config->get('tasklock');
        $lockedTasks[$taskId] = date('Y-m-d H:i:s');
        $this->config->set('tasklock', $lockedTasks);
    }

    public function isLockedTask(string $taskId): bool
    {
        $lockedTasks = $this->config->get('tasklock');
        if (isset($lockedTasks[$taskId])) {
            if (new \DateTime($lockedTasks[$taskId]) < (new \DateTime())->sub(\DateInterval::createFromDateString('1 hour'))) {
                $this->unlockTask($taskId);
            } else {
                return true;
            }
        }
        return false;
    }

    public function unlockTask(string $taskId): void
    {
        $lockedTasks = $this->config->get('tasklock');
        if (isset($lockedTasks[$taskId])) {
            unset($lockedTasks[$taskId]);
            $this->config->set('tasklock', $lockedTasks);
        }
    }

    public function getTask(string $id): ?TaskInterface
    {
        if (!isset($this->tasks[$id]) && isset($this->taskConfig[$id])) {
            $this->tasks[$id] = $this->createTaskById($id);
        }
        if (isset($this->tasks[$id])) {
            return $this->tasks[$id];
        }
        return null;
    }

    public function getTasksByName(string $name): array
    {
        $tasks = [];
        foreach ($this->taskConfig as $taskId => $task) {
            if ($task['name'] === $name) {
                $tasks[] = $this->getTask($taskId);
            }
        }
        return $tasks;
    }

    public function getTasksByType(string $type): array
    {
        $return = [];
        foreach ($this->taskConfig as $taskId => $task) {
            if ($task['type'] === $type) {
                $return[$taskId] = $this->getTask($taskId);
            }
        }
        return $return;
    }

    private function createTaskById(string $taskId): TaskInterface
    {
        if(!isset($this->taskConfig[$taskId])) {
            throw new \RuntimeException(sprintf('Task with ID %s not found', $taskId));
        }

        $taskConfig = $this->taskConfig[$taskId];

        $class = 'Sumedia\\WinestroApi\\Winestro\\Task\\' . ucfirst($taskConfig['type']) . 'Task';
        if (!class_exists($class)) {
            throw new \RuntimeException(sprintf('Task class "%s" does not exist.', $taskConfig['type']));
        }

        $task = $this->container->get($class);
        $task->init($taskConfig);
        return $task;
    }
}