<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\WinestroAPI\Setting\Service;

use Sumedia\WinestroAPI\Setting\SumediaWboSettingStruct;

interface SettingsServiceInterface
{
    /** @throws \Sumedia\WinestroAPI\Setting\Exception\WboSettingsInvalidException */
    public function getSettings(?string $salesChannelId = null, bool $inherited = true): SumediaWboSettingStruct;
    public function updateSettings(array $settings, ?string $salesChannelId = null): void;
}
