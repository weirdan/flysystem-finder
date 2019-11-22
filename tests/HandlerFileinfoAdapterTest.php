<?php
namespace Weirdan\FlysystemFinder\Tests;

use League\Flysystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Weirdan\FlysystemFinder\HandlerFileinfoAdapter;

class HandlerFileinfoAdapterTest extends TestCase
{
    public function setUp(): void
    {
        $this->root = vfsStream::setup('testroot');
        $this->fs = new Flysystem\Filesystem(new Flysystem\Adapter\Local(vfsStream::url('testroot'), 0));
    }
    /**
     * @test
     */
    public function hasFilenameMethod()
    {
        $adapter = new HandlerFileinfoAdapter(new Flysystem\File($this->fs, 'aa'));
    }
}
