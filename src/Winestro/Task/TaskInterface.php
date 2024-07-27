<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Task;

use Sumedia\WinestroApi\Winestro\ConnectionInterface;

interface TaskInterface
{
    public function init($taskConfig): void;
    public function getTaskConfig(): array;
    public function getParameters(): array;
    public function hasParameter(string $key): bool;
    public function getParameter(string $key): mixed;
    public function setParameter(string $key, mixed $value): void;
    public function removeParameter(string $key): void;
    public function getWinestroConnection(): ConnectionInterface;
    public function execute(TaskInterface $parentTask = null): void;
    public function getExtensions(): array;
}