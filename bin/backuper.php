<?php

require __DIR__ . '/../vendor/autoload.php';

use RuSPanzer\Backuper\Command\BackupCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$application->addCommands([
    new BackupCommand(),
]);

$application->run();