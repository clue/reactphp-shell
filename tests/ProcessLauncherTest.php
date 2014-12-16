<?php

use Clue\React\Shell\ProcessLauncher;

class ProcessLauncherTest extends TestCase
{
    private $loop;
    private $processLauncher;

    public function setUp()
    {
        $this->loop = $this->getMock('React\EventLoop\LoopInterface');
        $this->processLauncher = new ProcessLauncher($this->loop);
    }

    public function testProcessWillBeStarted()
    {
        $process = $this->getMockBuilder('React\ChildProcess\Process')->disableOriginalConstructor()->getMock();
        $process->stdout = $this->getMock('React\Stream\ReadableStreamInterface');
        $process->stdin = $this->getMock('React\Stream\WritableStreamInterface');

        $process->expects($this->once())->method('start');

        $shell = $this->processLauncher->createDeferredShell($process);

        $this->assertInstanceOf('Clue\React\Shell\DeferredShell', $shell);
    }
}
