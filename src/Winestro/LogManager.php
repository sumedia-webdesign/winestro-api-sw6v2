<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LogManager implements LogManagerInterface, LoggerInterface
{
    private string $taskId = '';

    private bool $errorHandlerSetted = false;

    private bool $voidErrorHandlerSetted = false;

    public function __construct(
        private LoggerInterface $debugLogger,
        private LoggerInterface $errorLogger,
        private LoggerInterface $taskLogger,
        private LoggerInterface $cronLogger
    ) {
        $this->taskId = uniqid();
    }

    public function setTaskId(string $taskId): void
    {
        $this->taskId = $taskId;
    }

    public function getTaskId(): string
    {
        return $this->taskId;
    }

    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->errorLogger->emergency("[$this->taskId] $message", $context);
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->errorLogger->alert("[$this->taskId] $message", $context);
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->errorLogger->critical("[$this->taskId] $message", $context);
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->errorLogger->error("[$this->taskId] $message", $context);
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->debugLogger->warning("[$this->taskId] $message", $context);
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->debugLogger->notice("[$this->taskId] $message", $context);
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->debugLogger->info("[$this->taskId] $message", $context);
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->debugLogger->debug("[$this->taskId] $message", $context);
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        switch ($level) {
            case LogLevel::EMERGENCY: $this->emergency($message, $context); break;
            case LogLevel::ALERT: $this->alert($message, $context); break;
            case LogLevel::CRITICAL: $this->critical($message, $context); break;
            case LogLevel::ERROR: $this->error($message, $context); break;
            case LogLevel::WARNING: $this->warning($message, $context); break;
            case LogLevel::NOTICE: $this->notice($message, $context); break;
            case LogLevel::INFO: $this->info($message, $context); break;
            case LogLevel::DEBUG: $this->debug($message, $context); break;
            default: $this->errorLogger->error("[$this->taskId] $message", $context);
        }
    }

    public function logTask(\Stringable|string $message, array $context = []): void
    {
        $this->taskLogger->info("[$this->taskId] $message", $context);
    }

    public function logCron(\Stringable|string $message, array $context = []): void
    {
        $this->cronLogger->info("[$this->taskId] $message", $context);
    }

    public function logException(\Throwable $e): void
    {
        $message =
            "[" . $this->taskId . "] ".
            $e->getMessage() . "\n in " .
            $e->getFile() . "\n line " .
            $e->getLine() . "\n" . $e->getTraceAsString();
        $this->errorLogger->error($message);
    }

    public function setErrorHandler(): void
    {
        if (!$this->errorHandlerSetted) {
            set_error_handler([$this, 'handleError']);
            $this->errorHandlerSetted = true;
        }
    }

    public function resetErrorHandler(): void
    {
        if ($this->errorHandlerSetted) {
            restore_error_handler();
        }
        $this->errorHandlerSetted = false;
    }

    public function isErrorHandlerSetted(): bool
    {
        return $this->errorHandlerSetted;
    }

    public function handleError($code, $message, $file, $line): bool
    {
        $exception = new \ErrorException($message, $code, E_ERROR, $file, $line);
        $this->logException($exception);
        return true;
    }

    public function setVoidErrorHandler(): void
    {
        if (!$this->voidErrorHandlerSetted) {
            set_error_handler([$this, 'voidHandleError']);
        }
    }

    public function resetVoidErrorHandler(): void
    {
        if ($this->voidErrorHandlerSetted) {
            restore_error_handler();
        }
        $this->voidErrorHandlerSetted = false;
    }

    public function isVoidErrorHandlerSetted(): bool
    {
        return $this->voidErrorHandlerSetted;
    }

    public function voidHandleError($code, $message, $file, $line): bool
    {
        return true;
    }

    public function getErrorLogger(): LoggerInterface
    {
        return $this->errorLogger;
    }
    public function getDebugLogger(): LoggerInterface
    {
        return $this->debugLogger;
    }
    public function getTaskLogger(): LoggerInterface
    {
        return $this->taskLogger;
    }
    public function getCronLogger(): LoggerInterface
    {
        return $this->cronLogger;
    }
}
