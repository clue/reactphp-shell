<?php

use Clue\React\Shell\DeferredShell;

class DeferredShellTest extends TestCase
{
    private $stream;

    public function setUp()
    {
        $this->stream = $this->getMockBuilder('React\Stream\DuplexStreamInterface')->getMock();
    }

    public function testExecuteWritesToStream()
    {
        $this->stream->expects($this->once())->method('write')->with($this->equalTo("echo asd\ndemo\necho asd\n"));

        $shell = new DeferredShell($this->stream);
        $shell->setEol("\n");
        $shell->setBounding('echo asd', "asd\n");

        $promise = $shell->execute('demo');
    }

    public function testEndWillClose()
    {
        $this->stream->expects($this->once())->method('close');

        $shell = new DeferredShell($this->stream);
        $shell->end();

        return $shell;
    }

    /**
     * @depends testEndWillClose
     * @param DeferredShell $shell
     */
    public function testEndedExecute(DeferredShell $shell)
    {
        $this->stream->expects($this->never())->method('write');

        $promise = $shell->execute('demo');
        $this->expectPromiseReject($promise);
    }

    public function testInteraction()
    {
        $this->stream->expects($this->once())->method('write')->with($this->equalTo("echo asd\ndemo\necho asd\n"));

        $shell = new DeferredShell($this->stream);
        $shell->setEol("\n");
        $shell->setBounding('echo asd', "asd\n");

        $shell->handleData('premature shell output');

        $promise = $shell->execute('demo');
        $this->expectPromiseResolveWith("output\n", $promise);

        $shell->handleData("as");
        $shell->handleData("d\nout");
        $shell->handleData("put\nasd\nadditional");
    }
}
