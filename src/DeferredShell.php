<?php

namespace Clue\React\Shell;

use React\Promise\Deferred;
use React\Stream\DuplexStreamInterface;
use RuntimeException;

class DeferredShell
{
    private $stream;

    private $pending = array();
    private $ending = false;

    private $bounding;
    private $boundingCommand;

    private $eol = PHP_EOL;

    private $buffer = '';
    private $started = false;

    public function __construct(DuplexStreamInterface $stream)
    {
        $this->stream = $stream;

        $this->stream->on('data', array($this, 'handleData'));
        $this->stream->on('end', array($this, 'handleEnd'));

        $this->setBounding('echo -n {{ bounding }}', md5(uniqid()));
    }

    /**
     * Sets end-of-line (EOL) string terminator to use
     *
     * @param string $eol
     */
    public function setEol($eol)
    {
        $this->eol = $eol;
    }

    /**
     * Sets bounding to print before and after calling execute()
     *
     * This bounding will be used to determine the start and end position of
     * each executed command.
     *
     * The $boundingText is a (pseudo-)random string that can be used to mark
     * the start and end position of every command. Make sure to use a sufficiently
     * unique string that does not occur anyway in your command output.
     * The $boundingText will be searched for as-is, make sure to append a
     * trailing EOL if your $boundingCommand prints an EOL that should not be
     * part of the command output.
     *
     * The $boundingCommand is responsible for writing out the $boundingText
     * to the STDOUT right before and right after calling execute().
     * You can use the "{{ bounding }}" placeholder that will be replaced with
     * the given $boundingText. The current EOL will automatically be written
     * after the $boundingCommand.
     *
     * @param string $boundingCommand the command used to write the bounding text to the STDOUT
     * @param string $boundingText    (optional) bounding text to use, defaults to a new random text
     * @see self::setEol()
     */
    public function setBounding($boundingCommand, $boundingText = null)
    {
        if ($boundingText === null) {
            $boundingText = md5(uniqid());
        }

        $this->boundingCommand = str_replace('{{ bounding }}', $boundingText, $boundingCommand);
        $this->bounding = $boundingText;
    }

    /**
     * Execute the given $command string in this shell instance
     *
     * @param string $command
     * @return Promise Promise<string> resolves with the command output or rejects with a RuntimeException
     * @see self::setBounding()
     */
    public function execute($command)
    {
        $deferred = new Deferred();

        if ($this->ending) {
            $deferred->reject(new RuntimeException('Shell is ending already'));
        } else {
            $this->pending []= $deferred;

            $this->sendCommand($command);
        }

        return $deferred->promise();
    }

    /**
     * Soft-close the shell once all pending commands have been fulfilled
     *
     * @see self::close()
     */
    public function end()
    {
        $this->ending = true;

        if (!$this->pending) {
            $this->close();
        }
    }

    /**
     * hard-close the shell now and reject all pending commands
     *
     * @see self::end()
     */
    public function close()
    {
        $this->ending = true;

        foreach ($this->pending as $deferred) {
            $deferred->reject(new RuntimeException('Shell is ending'));
        }
        $this->pending = array();
        $this->buffer = '';

        $this->stream->removeListener('data', array($this, 'handleData'));
        $this->stream->removeListener('end', array($this, 'handleEnd'));

        $this->stream->close();
    }

    private function sendCommand($command)
    {
        $this->stream->write($this->boundingCommand . $this->eol . $command . $this->eol . $this->boundingCommand . $this->eol);
    }

    /** @internal */
    public function handleData($data)
    {
        // temporarily buffer everything
        $this->buffer .= $data;

        do {
            // search bounding in buffer
            $pos = strpos($this->buffer, $this->bounding);

            // bounding has not been found
            if ($pos === false) {
                if (!$this->started) {
                    // received junk before actual start
                    // trim its length to avoid buffering useless data
                    $this->buffer = (string)substr($this->buffer, 1 - strlen($this->bounding));
                }

                return;
            }

            // bounding has been found
            if ($this->started) {
                // already started, so we found the end bounding
                $data = (string)substr($this->buffer, 0, $pos);

                // advance buffer behind end of bounding
                $this->buffer = (string)substr($this->buffer, $pos + strlen($this->bounding));
                $this->started = false;

                $deferred = array_shift($this->pending);
                /* @var $deferred Deferred */

                $deferred->resolve($data);

                // last pending message received => close shell
                if ($this->ending && !$this->pending) {
                    $this->close();
                }
            } else {
                // not already started, so we found the start bounding
                $this->buffer = (string)substr($this->buffer, $pos + strlen($this->bounding));
                $this->started = true;
            }
        } while ($this->buffer !== '');
    }

    /** @internal */
    public function handleEnd()
    {
        // STDOUT closed => assume process is closed
        $this->close();
    }
}
