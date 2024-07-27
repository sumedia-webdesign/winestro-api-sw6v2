<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class Config implements ConfigInterface
{
    const CONFIG_PREFIX = 'SumediaWinestroApi';

    public function __construct(private SystemConfigService $systemConfigService){}

    public function set(string $key, mixed $value): void
    {
        $this->systemConfigService->set(self::CONFIG_PREFIX . '.config.' . $key, $value);
    }

    public function get(string $key): mixed
    {
        return $this->systemConfigService->get(self::CONFIG_PREFIX . '.config.' . $key);
    }

    public function toArray(): array
    {
        return $this->systemConfigService->get(self::CONFIG_PREFIX . '.config');
    }
}