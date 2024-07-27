<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro;

use Sumedia\WinestroApi\Winestro\Task\TaskInterface;

interface TaskManagerInterface
{
    public function getTaskConfig(): array;
    public function getParameters(): array;
    public function hasParameter(string $key): bool;
    public function getParameter(string $key): mixed;
    public function setParameter(string $key, mixed $value): void;
    public function removeParameter(string $key): void;
    public function getTask(string $id): ?TaskInterface;
    public function getTasksByName(string $name): array;
    public function getTasksByType(string $type): array;
}