<?php

namespace Clue\React\Shell;

use React\ChildProcess\Process;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Clue\React\Shell\DeferredShell;
use React\Stream\CompositeStream;

class ProcessLauncher
{
    /** @var LoopInterface */
    private $loop;

    /**
     * This class takes an optional `LoopInterface|null $loop` parameter that can be used to
     * pass the event loop instance to use for this object. You can use a `null` value
     * here in order to use the [default loop](https://github.com/reactphp/event-loop#loop).
     * This value SHOULD NOT be given unless you're sure you want to explicitly use a
     * given event loop instance.
     *
     * @param ?LoopInterface $loop
     */
    public function __construct(LoopInterface $loop = null)
    {
        $this->loop = $loop ?: Loop::get();
    }

    /**
     * launch the given interactive $command shell
     *
     * Its STDOUT will be used to parse responses, the STDIN will be used
     * to pass commands.
     *
     * If the command prints output to STDERR, make sure to redirect it to
     * STDOUT by appending " 2>&1".
     *
     * @param string|Process $process accepts either a command string to execute or a Process instance
     * @return DeferredShell
     */
    public function createDeferredShell($process)
    {
        if (!($process instanceof Process)) {
            $process = new Process($process);
        }

        $process->start($this->loop);

        $stream = new CompositeStream($process->stdout, $process->stdin);

        // forcefully terminate process when stream closes
        $stream->on('close', function () use ($process) {
            if ($process->isRunning()) {
                $process->terminate(defined('SIGKILL') ? SIGKILL : null);
            }
        });

        return new DeferredShell($stream);
    }
}
