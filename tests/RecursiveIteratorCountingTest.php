<?php
namespace Weirdan\FlysystemFinder\Tests;

use Countable;
use PHPUnit_Framework_TestCase as PHPUnit;
use League\Flysystem\Adapter;
use League\Flysystem\Filesystem;
use org\bovigo\vfs\vfsStream;
use Weirdan\FlysystemFinder\RecursiveIterator;

class RecursiveIteratorCountingTest extends PHPUnit
{
    public function setUp()
    {
        $this->root = vfsStream::setup('testroot');
        $this->fs = new Filesystem(new Adapter\Local(vfsStream::url('testroot'), 0));
    }

    /**
     * @test
     */
    public function iteratorIsCountable()
    {
        $it = new RecursiveIterator($this->fs);
        $this->assertInstanceOf(Countable::class, $it);
    }

    /**
     * @test
     */
    public function emptyDirHasZeroCount()
    {
        $it = new RecursiveIterator($this->fs);
        $this->assertCount(0, $it);
    }

    /**
     * @test
     */
    public function notEmptyDirHasNonZeroCount()
    {
        $this->fs->put('a', 'aa');
        $it = new RecursiveIterator($this->fs);
        $this->assertNotCount(0, $it);
    }

    /**
     * @test
     */
    public function onlyImmediateChildrenAreCounted()
    {
        $this->fs->put('a/aa', 'aa');
        $this->fs->put('a/bb', 'bb');
        $it = new RecursiveIterator($this->fs);
        $this->assertCount(1, $it);
    }
}
