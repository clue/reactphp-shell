# clue/reactphp-shell

[![CI status](https://github.com/clue/reactphp-shell/actions/workflows/ci.yml/badge.svg)](https://github.com/clue/reactphp-shell/actions)
[![installs on Packagist](https://img.shields.io/packagist/dt/clue/shell-react?color=blue&label=installs%20on%20Packagist)](https://packagist.org/packages/clue/shell-react)

Run async commands within any interactive shell command, built on top of [ReactPHP](https://reactphp.org/).

> Note: This project is in beta stage! Feel free to report any issues you encounter.

## Quickstart example

Once [installed](#install), you can use the following code to run an interactive
bash shell and issue some commands within:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

$launcher = new Clue\React\Shell\ProcessLauncher();

$shell = $launcher->createDeferredShell('bash');

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
```

See also the [examples](examples/):

* [Run shell commands within a bash shell](examples/bash.php)
* [Run PHP code within an interactive PHP shell](examples/php.php)
* [Run shell commands within a docker container](examples/docker.php)

## Install

The recommended way to install this library is [through Composer](https://getcomposer.org/).
[New to Composer?](https://getcomposer.org/doc/00-intro.md)

This will install the latest supported version:

```bash
composer require clue/shell-react:^0.2
```

See also the [CHANGELOG](CHANGELOG.md) for details about version upgrades.

This project aims to run on any platform and thus does not require any PHP
extensions and supports running on legacy PHP 5.3 through current PHP 8+.
It's *highly recommended to use the latest supported PHP version* for this project.

## Tests

To run the test suite, you first need to clone this repo and then install all
dependencies [through Composer](https://getcomposer.org/):

```bash
composer install
```

To run the test suite, go to the project root and run:

```bash
vendor/bin/phpunit
```

## License

MIT
