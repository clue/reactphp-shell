<?php

require __DIR__ . '/../vendor/autoload.php';

$launcher = new Clue\React\Shell\ProcessLauncher();

$shell = $launcher->createDeferredShell('docker run -i --rm debian bash');

$shell->execute('id')->then(function ($result) {
    var_dump('current user', $result);
}, function (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
});

$shell->execute('env')->then(function ($env) {
    var_dump('env', $env);
}, function (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
});

$shell->end();
