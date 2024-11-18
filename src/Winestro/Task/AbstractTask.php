<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Task;

use Sumedia\WinestroApi\Config;
use Sumedia\WinestroApi\RepositoryManager;
use Sumedia\WinestroApi\Winestro\ConnectionInterface;
use Sumedia\WinestroApi\Winestro\Exception\TaskLockerException;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;
use Sumedia\WinestroApi\Winestro\RequestManager;
use Sumedia\WinestroApi\Winestro\Task\Extension\ExtensionInterface;
use Sumedia\WinestroApi\Winestro\TaskManager;
use Symfony\Component\DependencyInjection\Container;

abstract class AbstractTask implements TaskInterface, \ArrayAccess
{
    private array $taskConfig = [];
    private array $extensions = [];
    private array $parameters = [];
    protected ?TaskInterface $parentTask = null;

    public function __construct(
        protected Container $container,
        protected Config $config,
        protected TaskManager $taskManager,
        protected RepositoryManager $repositoryManager,
        protected RequestManager $requestManager,
        protected LogManagerInterface $logManager
    ){}

    public function init($taskConfig): void
    {
        $this->taskConfig = $taskConfig;
        foreach ($this->taskConfig['extensions'] as $extensionId => $extensionConfig) {
            $this->extensions[$extensionId] = $this->createExtension($extensionConfig);
        }
    }

    protected function _execute(?TaskInterface $parentTask, callable $callback): void
    {
        $taskLocked = false;
        try {
            $this->setParentTask($parentTask);

            $this->logManager->newLogId();
            $this->logManager->logProcess('[task run ' . $this['id'] . ']');
            if (!$this['enabled']['enabled']) {
                $this->logManager->logProcess('[task deactivated]');
                return;
            }

            $this->lockTask($this['id']);

            $callback();

            $this->logManager->logProcess('[task execute children]');

            $executedTasks = null === $parentTask ? [] : $parentTask->getParameter('executedTasks');
            $executedTasks[] = $this['id'];
            $this->setParameter('executedTasks', $executedTasks);
            $this->executeChildTasks($this);

            $this->logManager->logProcess('[task successful]');
        } catch (TaskLockerException $e) {
            $taskLocked = true;
            $this->logManager->logProcess('[task locked]');
            $this->logManager->logProcess($e);
            throw $e;
        } catch (\Exception $e) {
            $this->logManager->logProcess('[task failed]');
            $this->logManager->logProcess($e);
            throw $e;
        } finally {
            if (!$taskLocked) {
                $this->unlockTask($this['id']);
            }
            $this->logManager->resetLogId();
        }
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

    public function getTaskConfig(): array
    {
        return $this->taskConfig;
    }

    public function getWinestroConnection(): ConnectionInterface
    {
        $winestroConnectionId = $this->getTaskConfig()['winestroConnectionId'];
        $winestroConnections = $this->config->get('winestroConnections');
        $winestroConnectionConfig = $winestroConnections[$winestroConnectionId];

        $connection = $this->container->get('Sumedia\WinestroApi\Winestro\Connection');
        $connection->setUrl($winestroConnectionConfig['url'] . '/wbo-API.php');
        $connection->setParameter('UID', (string) $winestroConnectionConfig['userId']);
        $connection->setParameter('apiShopID', (string) $winestroConnectionConfig['shopId']);
        $connection->setParameter('apiUSER', $winestroConnectionConfig['secretId']);
        $connection->setParameter('apiCODE', $winestroConnectionConfig['secretCode']);

        return $connection;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }

    private function setParentTask(?TaskInterface $parentTask = null): void
    {
        if (null !== $parentTask) {
            $this->setParameter('executedTasks', $parentTask->getParameter('executedTasks'));
            $this->parentTask = $parentTask;
        }
    }

    private function lockTask(string $taskId): void
    {
        $this->taskManager->lockTask($taskId);
        $this->lockExecutedTask($taskId);
    }

    private function unlockTask(string $taskId): void
    {
        $this->taskManager->unlockTask($taskId);
    }

    private function isExecutedTask($taskId): bool
    {
        $executedTasks = $this->getParameter('executedTasks');
        if (isset($executedTasks[$taskId])) {
            if (new \DateTime($executedTasks[$taskId]) < (new \DateTime())->sub(\DateInterval::createFromDateString('1 hour'))) {
                return true;
            }
            $this->unlockExecutedTasks($taskId);
        }
        return false;
    }

    private function lockExecutedTask(string $taskId): void
    {
        if ($this->isExecutedTask($taskId)) {
            $this->logManager->logProcess('[task recursion]');
            throw new \RuntimeException("task recursion $taskId");
        }
        $executedTasks = $this->getParameter('executedTasks');
        $executedTasks[] = $taskId;
        $this->setParameter('executedTasks', $executedTasks);
    }

    private function unlockExecutedTasks(string $taskId): void
    {
        $executedTasks = $this->getParameter('executedTasks');
        if (false !== $key = array_search($taskId, $executedTasks)) {
            unset($executedTasks[$key]);
            $this->setParameter('executedTasks', $executedTasks);
        }
    }

    private function executeChildTasks(TaskInterface $parentTask): void
    {
        foreach ($this['execute'] as $taskId) {
            if (!$this->isExecutedTask($taskId)) {
                $task = $this->taskManager->getTask($taskId);
                $task->execute($this);
            }
        }
    }

    private function createExtension(array $extensionConfig): ExtensionInterface
    {
        $type = $extensionConfig['type'];
        $class = 'Sumedia\\WinestroApi\\Winestro\\Task\\Extension\\' . ucfirst($type) . 'Extension';
        if (!class_exists($class)) {
            throw new \RuntimeException(sprintf('Class "%s" does not exist.', $class));
        }

        $extension = $this->container->get($class);
        $extension->init($extensionConfig);
        return $extension;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->taskConfig[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->taskConfig[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->taskConfig[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        if (isset($this->taskConfig[$offset])) {
            unset($this->taskConfig[$offset]);
        }
    }
}
