# clue/shell-react [![Build Status](https://travis-ci.org/clue/php-shell-react.svg?branch=master)](https://travis-ci.org/clue/php-shell-react)

Run async commands within any interactive shell command, built on top of React PHP.

> Note: This project is in beta stage! Feel free to report any issues you encounter.

## Quickstart example

Once [installed](#install), you can use the following code to run an interactive
bash shell and issue some commands within:

```php
$loop = React\EventLoop\Factory::create();
$launcher = new ProcessLauncher($loop);

$shell = $launcher->createDeferredShell('bash');

$shell->execute('echo -n $USER')->then(function ($result) {
    var_dump('current user', $result);
});

$shell->execute('env | sort | head -n10')->then(function ($env) {
    var_dump('env', $env);
});

$shell->end();

$loop->run();
```

See also the [examples](examples):

* [Run shell commands within a bash shell](examples/bash.php)
* [Run PHP code within an interactive PHP shell](examples/php.php)
* [Run shell commands within a docker container](examples/docker.php)

## Install

The recommended way to install this library is [through composer](http://getcomposer.org). [New to composer?](http://getcomposer.org/doc/00-intro.md)

```JSON
{
    "require": {
        "clue/shell-react": "~0.2.0"
    }
}
```

## Tests

To run the test suite, you first need to clone this repo and then install all
dependencies [through Composer](https://getcomposer.org):

```bash
$ composer install
```

To run the test suite, go to the project root and run:

```bash
$ php vendor/bin/phpunit
```

## License

MIT
