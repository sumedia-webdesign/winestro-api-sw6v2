<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Task\Extension;

use Sumedia\WinestroApi\ConfigInterface;
use Sumedia\WinestroApi\Winestro\ConnectionInterface;
use Symfony\Component\DependencyInjection\Container;

abstract class AbstractExtension implements ExtensionInterface, \ArrayAccess
{
    private array $extensionConfig = [];

    public function __construct(
        protected Container $container,
        protected ConfigInterface $config
    ){}

    public function init(array $extensionConfig): void
    {
        $this->extensionConfig = $extensionConfig;
    }

    public function getExtensionConfig(): array
    {
        return $this->extensionConfig;
    }

    public function getWinestroConnection(): ConnectionInterface
    {
        $winestroConnectionId = $this->getExtensionConfig()['winestroConnectionId'];
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

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->extensionConfig[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->extensionConfig[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->extensionConfig[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        if (isset($this->extensionConfig[$offset])) {
            unset($this->extensionConfig[$offset]);
        }
    }
}