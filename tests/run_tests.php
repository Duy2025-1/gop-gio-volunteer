<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once __DIR__ . '/BusinessLogicTest.php';

$runner = new \Tests\BusinessLogicTest();
$success = $runner->runAll();
exit($success ? 0 : 1);
