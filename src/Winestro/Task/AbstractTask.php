<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Task;

use Sumedia\WinestroApi\Config;
use Sumedia\WinestroApi\RepositoryManager;
use Sumedia\WinestroApi\Winestro\ConnectionInterface;
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

    protected function _execute(TaskInterface $parentTask = null, callable $callback): void
    {
        try {
            $this->parentTask = $parentTask;

            $lastLogTaskId = $this->logManager->getTaskId();
            $this->logManager->setTaskId($lastLogTaskId . '-' . uniqid());

            $this->logManager->logTask('[task run: ' . $this['id'] . ']');

            if (!$this['enabled']['enabled']) {
                $this->logManager->logTask('[task deactivated]');
                return;
            }

            $executedTasks = $this->taskManager->getParameter('executedTasks') ?? [];
            if (in_array($this['id'], $executedTasks)) {
                $this->logManager->logTask('[task recursion]');
                return;
            }

            $running = $this->config->get('running') ?? [];
            if (isset($running[$this['id']])) {
                $runningDate = (new \DateTime($running[$this['id']]))->add(\DateInterval::createFromDateString('1 hour'));
                if (new \DateTime() < $runningDate) {
                    $this->logManager->logTask('[task running]');
                    return;
                }
            }
            $running[$this['id']] = date('Y-m-d H:i:s');
            $this->config->set('running', $running);

            $callback();

            $this->logManager->logTask('[task execute children]');

            $executedTasks[] = $this['id'];
            $this->taskManager->setParameter('executedTasks', $executedTasks);
            $this->executeChildTasks($this);

            if (!$this->parentTask) {
                $this->taskManager->removeParameter('executedTasks');
            }

            $running = $this->config->get('running') ?? [];
            if (isset($running[$this['id']])) {
                unset($running[$this['id']]);
            }
            $this->config->set('running', $running);

            $this->logManager->logTask('[task successful]');
        } catch (\Exception $e) {
            $this->logManager->logTask('[task failed]');
            throw $e;
        } finally {
            $this->logManager->setTaskId($lastLogTaskId);
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

    public function executeChildTasks(TaskInterface $parentTask): void
    {
        foreach ($this['execute'] as $taskId) {
            if (!in_array($taskId, $this->taskManager->getParameter('executedTasks'))) {
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
