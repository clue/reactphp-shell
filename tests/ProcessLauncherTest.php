<?php

use Clue\React\Shell\ProcessLauncher;

class ProcessLauncherTest extends TestCase
{
    private $loop;
    private $processLauncher;

    public function setUp()
    {
        $this->loop = $this->createMock('React\EventLoop\LoopInterface');
        $this->processLauncher = new ProcessLauncher($this->loop);
    }

    public function testProcessWillBeStarted()
    {
        $process = $this->getMockBuilder('React\ChildProcess\Process')->disableOriginalConstructor()->getMock();
        $process->stdout = $this->createMock('React\Stream\ReadableStreamInterface');
        $process->stdin = $this->createMock('React\Stream\WritableStreamInterface');

        $process->expects($this->once())->method('start');

        $shell = $this->processLauncher->createDeferredShell($process);

        $this->assertInstanceOf('Clue\React\Shell\DeferredShell', $shell);
    }


    public function testClosingStreamTerminatesRunningProcess()
    {
        $process = $this->getMockBuilder('React\ChildProcess\Process')->disableOriginalConstructor()->getMock();
        $process->stdout = $this->createMock('React\Stream\ReadableStreamInterface');
        $process->stdin = $this->createMock('React\Stream\WritableStreamInterface');

        $process->stdout->expects($this->any())->method('isReadable')->willReturn(true);
        $process->stdin->expects($this->any())->method('isWritable')->willReturn(true);

        $process->expects($this->once())->method('isRunning')->will($this->returnValue(true));
        $process->expects($this->once())->method('terminate')->with($this->equalTo(SIGKILL));

        $shell = $this->processLauncher->createDeferredShell($process);

        $shell->close();
    }

    public function testClosingStreamOfNonRunningProcessWillNotTerminate()
    {
        $process = $this->getMockBuilder('React\ChildProcess\Process')->disableOriginalConstructor()->getMock();
        $process->stdout = $this->createMock('React\Stream\ReadableStreamInterface');
        $process->stdin = $this->createMock('React\Stream\WritableStreamInterface');

        $process->stdout->expects($this->any())->method('isReadable')->willReturn(true);
        $process->stdin->expects($this->any())->method('isWritable')->willReturn(true);

        $process->expects($this->once())->method('isRunning')->will($this->returnValue(false));
        $process->expects($this->never())->method('terminate');

        $shell = $this->processLauncher->createDeferredShell($process);

        $shell->close();
    }
}
