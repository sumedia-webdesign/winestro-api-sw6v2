<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro;

interface CronManagerInterface
{
    public function getTaskIdsByTime(string $time): array;
}