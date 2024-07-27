<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro;

use Psr\Log\LoggerInterface;

interface LogManagerInterface
{
    public function setTaskId(string $taskId): void;
    public function getTaskId(): string;
    public function logTask(\Stringable|string $message, array $context = []): void;
    public function logCron(\Stringable|string $message, array $context = []): void;
    public function logException(\Throwable $e): void;
    public function setErrorHandler(): void;
    public function resetErrorHandler(): void;
    public function isErrorHandlerSetted(): bool;
    public function setVoidErrorHandler(): void;
    public function resetVoidErrorHandler(): void;
    public function isVoidErrorHandlerSetted(): bool;
    public function getErrorLogger(): LoggerInterface;
    public function getDebugLogger(): LoggerInterface;
    public function getTaskLogger(): LoggerInterface;
    public function getCronLogger(): LoggerInterface;
}