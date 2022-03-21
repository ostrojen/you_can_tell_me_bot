#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use YouCanTellMeBot\Command\TestCommand;

$application = new Application();

// ... register commands
$application->add(new TestCommand());

$application->run();