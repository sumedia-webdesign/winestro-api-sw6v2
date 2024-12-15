<?php

use Shopware\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;
use Symfony\Component\Dotenv\Dotenv;
(new Dotenv())->bootEnv(dirname(getenv('IDE_PHPUNIT_CUSTOM_LOADER')). '/../.env.test');
require __DIR__ . '/TestSuiteHelper.php';

$classLoader = require getenv('IDE_PHPUNIT_CUSTOM_LOADER');

KernelLifecycleManager::prepare($classLoader);