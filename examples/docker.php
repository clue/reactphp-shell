<?php

use Clue\React\Shell\ProcessLauncher;

require __DIR__ . '/../vendor/autoload.php';

$launcher = new ProcessLauncher();

$shell = $launcher->createDeferredShell('docker run -i --rm debian bash');

$shell->execute('id')->then(function ($result) {
    var_dump('current user', $result);
});

$shell->execute('env')->then(function ($env) {
    var_dump('env', $env);
});

$shell->end();
