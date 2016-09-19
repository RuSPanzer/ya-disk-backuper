<?php

require __DIR__ . '/../vendor/autoload.php';

use RuSPanzer\Backuper\Command as Commands;
use Symfony\Component\Console\Application;

$application = new Application();

$application->addCommands([
    new Commands\BackupCommand(),
    new Commands\DecryptCommand(),
]);

$application->run();