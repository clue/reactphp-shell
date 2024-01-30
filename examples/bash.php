<?php

require __DIR__ . '/../vendor/autoload.php';

$launcher = new Clue\React\Shell\ProcessLauncher();

$shell = $launcher->createDeferredShell('bash 2>&1');

$shell->execute('echo -n $USER')->then(function ($result) {
    var_dump('current user', $result);
}, function (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
});

$shell->execute('env | sort | head -n10')->then(function ($env) {
    var_dump('env', $env);
}, function (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
});

$shell->end();
