<?php

use Clue\React\Shell\ProcessLauncher;

require __DIR__ . '/../vendor/autoload.php';

$launcher = new ProcessLauncher();

$shell = $launcher->createDeferredShell('php -a');
$shell->setBounding("echo '{{ bounding }}';");

$shell->execute('$var = "hello";');
$shell->execute('$var = $var . " world";');

$shell->execute(<<<'CODE'
for ($i=0; $i<3; ++$i) {
    echo $var . '!';
}
CODE
)->then(function ($output) {
    echo 'Program output: ' . PHP_EOL . $output . PHP_EOL;
});

$shell->end();
