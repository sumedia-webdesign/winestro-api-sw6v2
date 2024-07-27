<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Request;

abstract class AbstractRequest implements RequestInterface
{
    private array $parameters = [];

    public function setParameter(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    public function getParameter(string $key): mixed
    {
        return $this->parameters[$key];
    }

    public function hasParameter(string $key): bool
    {
        return isset($this->parameters[$key]);
    }

    public function removeParameter($key): void
    {
        if ($this->hasParameter($key)) {
            unset($this->parameters[$key]);
        }
    }

    public function getParameters(): array
    {
        $data = [];
        foreach (array_keys($this->parameters) as $key) {
            $data[$key] = $this->getParameter($key);
        }
        return $data;
    }
}
