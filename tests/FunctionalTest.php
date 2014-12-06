<?php

use React\EventLoop\Factory;
use Clue\React\Shell\ProcessLauncher;

class FunctionalTest extends TestCase
{
    private $loop;
    private $launcher;

    public function setUp()
    {
        $this->loop = Factory::create();
        $this->launcher = new ProcessLauncher($this->loop);
    }

    public function testEndEmptyClosesImmediately()
    {
        $shell = $this->launcher->createDeferredShell('cat');

        $shell->end();

        $this->loop->run();
    }

    public function testCloseEmpty()
    {
        $shell = $this->launcher->createDeferredShell('cat');

        $shell->close();

        $this->loop->run();
    }

    public function testExecuteThenEnd()
    {
        $shell = $this->launcher->createDeferredShell('bash');

        $promise = $shell->execute('echo hallo');

        $this->expectPromiseResolveWith("hallo\n", $promise);

        $shell->end();

        $this->loop->run();
    }

    public function testExecuteLengthyOutput()
    {
        $shell = $this->launcher->createDeferredShell('bash');

        $promise = $shell->execute('env && env && env && env && env');
        $this->expectPromiseResolve($promise);

        $shell->end();

        $this->loop->run();
    }

    public function testClosePendingWillBeRejected()
    {
        $shell = $this->launcher->createDeferredShell('bash');

        $promise = $shell->execute('echo hello');
        $shell->close();

        $this->expectPromiseReject($promise);

        $this->loop->run();
    }

    public function testEndThenExecuteRejectsImmediately()
    {
        $shell = $this->launcher->createDeferredShell('bash');

        $shell->end();

        $promise = $shell->execute('echo hello');
        $this->expectPromiseReject($promise);
    }

    public function testExecuteInvalidResolves()
    {
        $shell = $this->launcher->createDeferredShell('bash');

        $promise = $shell->execute('some-random-invalid-command');
        $this->expectPromiseResolve($promise);

        $shell->end();

        $this->loop->run();
    }

    public function testExitingShellWillRejectAllExecutes()
    {
        $shell = $this->launcher->createDeferredShell('bash');

        $promise = $shell->execute('exit');
        $this->expectPromiseReject($promise);

        $promise = $shell->execute('echo hello');
        $this->expectPromiseReject($promise);

        $this->loop->run();
    }

    public function testPhpShell()
    {
        // TODO: skipped for lack of compatibility with HHVM
        return;

        $shell = $this->launcher->createDeferredShell('php -a');
        $shell->setBounding('echo "{{ bounding }}";');

        $shell->execute('$var = "hello";');
        $shell->execute('$var .= " world";');

        $promise = $shell->execute('echo $var;');
        $this->expectPromiseResolveWith('hello world', $promise);

        $shell->end();

        $this->loop->run();
    }
}
