<?php
namespace Weirdan\FlysystemFinder\Tests;

use RecursiveIteratorIterator;
use PHPUnit_Framework_TestCase as PHPUnit;
use League\Flysystem\Adapter;
use League\Flysystem\Filesystem;
use org\bovigo\vfs\vfsStream;
use Weirdan\FlysystemFinder\RecursiveIterator;

class RecursiveIteratorIterationTest extends PHPUnit
{
    public function setUp()
    {
        $this->root = vfsStream::setup('testroot');
        $this->fs = new Filesystem(new Adapter\Local(vfsStream::url('testroot'), 0));
    }

    /**
     * @test
     * @testdox Implements SPL RecursiveIterator
     */
    public function implementsSplRecursiveIterator()
    {
        $it = new RecursiveIterator($this->fs);
        $this->assertInstanceOf(\RecursiveIterator::class, $it);
    }

    /**
     * @test
     */
    public function startsWithFirstItem()
    {
        $this->fs->put('a', 'a-data');
        $it = new RecursiveIterator($this->fs);
        $first = $it->current();
        $this->assertTrue($first->isFile());
        $this->assertEquals('a', $first->getPath());
    }

    /**
     * @test
     */
    public function nextAdvancesToSecondItem()
    {
        $this->fs->put('a', 'aaa');
        $this->fs->put('b', 'bbb');
        $it = new RecursiveIterator($this->fs);
        $it->next();
        $this->assertEquals('b', $it->current()->getPath());
    }

    /**
     * @test
     */
    public function filesDoNotHaveChildren()
    {
        $this->fs->put('a', 'aa');
        $it = new RecursiveIterator($this->fs);
        $this->assertFalse($it->hasChildren());
    }

    /**
     * @test
     */
    public function directoriesMayHaveChildren()
    {
        $this->fs->put('a/b', 'bb');
        $it = new RecursiveIterator($this->fs);
        $this->assertTrue($it->hasChildren());
    }

    /**
     * @test
     */
    public function directoriesMayHaveNoChildren()
    {
        $this->fs->createDir('a');
        $it = new RecursiveIterator($this->fs);
        $this->assertFalse($it->hasChildren());
    }

    /**
     * @test
     */
    public function subdirectoriesCanBeIteratedOver()
    {
        $this->fs->put('a/b', 'bb');
        $this->fs->put('a/bb', 'cc');
        $it = new RecursiveIterator($this->fs);
        $subIt = $it->getChildren();
        $this->assertInstanceOf(RecursiveIterator::class, $subIt);
        $this->assertEquals('a/b', $subIt->current()->getPath());
        $subIt->next();
        $this->assertEquals('a/bb', $subIt->current()->getPath());
    }

    /**
     * @test
     */
    public function pathesAreUsedAsKeys()
    {
        $this->fs->put('a', 'aa');
        $this->fs->put('b/bb', 'bb');
        $it = new RecursiveIterator($this->fs);
        $this->assertEquals('a', $it->key());
        $it->next();
        $this->assertEquals('b/bb', $it->getChildren()->key());
    }

    /**
     * @test
     */
    public function iteratorIsValidAtStartOfNonEmptyDirectory()
    {
        $this->fs->put('b/bb', 'bb');
        $it = new RecursiveIterator($this->fs);
        $this->assertTrue($it->valid());
    }

    /**
     * @test
     */
    public function iteratorIsInvalidAtStartOfEmptyDirectory()
    {
        $it = new RecursiveIterator($this->fs);
        $this->assertFalse($it->valid());
    }

    /**
     * @test
     */
    public function iteratorIsInvalidAfterIterationEnd()
    {
        $it = new RecursiveIterator($this->fs);
        $it->next();
        $this->assertFalse($it->valid());
    }

    /**
     * @test
     */
    public function iteratorIsRewindable()
    {
        $this->fs->put('a', 'aa');
        $this->fs->put('b', 'bb');
        $it = new RecursiveIterator($this->fs);
        $it->next();
        $this->assertEquals('b', $it->current()->getPath());
        $it->rewind();
        $this->assertEquals('a', $it->current()->getPath());
    }

    /**
     * @test
     */
    public function iteratorCanBeConvertedToArray()
    {
        $this->fs->put('a', 'aa');
        $this->fs->put('b/bb', 'bb');
        $it = new RecursiveIterator($this->fs);
        $arr = iterator_to_array($it);
        $this->assertArrayHasKey('a', $arr);
        $this->assertArrayHasKey('b', $arr);
    }

    /**
     * @test
     * @testdox Iterator can be flattened with RecursiveIteratorIterator
     */
    public function iteratorCanBeFlattenedWithRecursiveIteratorIterator()
    {
        $files = ['a', 'b/1', 'b/2', 'c/1'];
        foreach ($files as $file) {
            $this->fs->put($file, 'dummy content');
        }

        $it = new RecursiveIterator($this->fs);

        $rii = iterator_to_array(new RecursiveIteratorIterator($it));
        foreach ($files as $file) {
            $this->assertArrayHasKey($file, $rii);
        }
    }
}
