<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\WinestroApi\Setting\Service;

use Sumedia\WinestroApi\Setting\SumediaWboSettingStruct;

interface SettingsServiceInterface
{
    /** @throws \Sumedia\WinestroApi\Setting\Exception\WboSettingsInvalidException */
    public function getSettings(?string $salesChannelId = null, bool $inherited = true): SumediaWboSettingStruct;
    public function updateSettings(array $settings, ?string $salesChannelId = null): void;
}
