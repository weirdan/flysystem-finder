<?php
namespace Weirdan\FlysystemFinder\Tests;

use Iterator;
use Countable;
use RecursiveIteratorIterator;
use PHPUnit_Framework_TestCase as PHPUnit;
use League\Flysystem\Adapter;
use League\Flysystem\Filesystem;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Finder;
use Weirdan\FlysystemFinder\RecursiveIterator;

class RecursiveIteratorTest extends PHPUnit
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
     * @testdox Iterator could be flattened with RecursiveIteratorIterator
     */
    public function iteratorCouldBeFlattenedWithRecursiveIteratorIterator()
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

    /**
     * @test
     * @testdox Can be filtered with SF FilterIterator by filename
     */
    public function canBeFilteredWithSFFiltersByFilename()
    {
        $this->fs->put('a/aa', 'aa');
        $this->fs->put('a/bb', 'bb');

        $it = new RecursiveIterator($this->fs);

        $filtered = $this->filtered($it, Finder\Iterator\FilenameFilterIterator::class, ['bb'], []);
        $this->assertCount(1, iterator_to_array($filtered));
    }

    protected function filtered(RecursiveIterator $it, string $by, ...$args)
    {
        return new $by(new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST), ...$args);
    }

    protected function justFiles(Iterator $it)
    {
        return new Finder\Iterator\FileTypeFilterIterator($it, Finder\Iterator\FileTypeFilterIterator::ONLY_FILES);
    }

    /**
     * @test
     * @testdox Can be filtered with SF FilterIterator by pathname
     */
    public function canBeFilteredWithSFFiltersByPathName()
    {
        $this->fs->put('a/aa', 'aa');
        $this->fs->put('a/bb', 'aa');

        $it = new RecursiveIterator($this->fs);

        $filtered = $this->filtered($it, Finder\Iterator\PathFilterIterator::class, ['a/bb'], []);

        $this->assertCount(1, iterator_to_array($filtered));
    }

    /**
     * @test
     */
    public function canBeFilteredWithSFFiltersByFiletypeDir()
    {
        $this->fs->put('a/aa', 'aaaa');

        $it = new RecursiveIterator($this->fs);

        $filtered = $this->filtered(
            $it,
            Finder\Iterator\FileTypeFilterIterator::class,
            Finder\Iterator\FileTypeFilterIterator::ONLY_DIRECTORIES
        );

        $this->assertCount(1, iterator_to_array($filtered));
        $filtered->rewind();
        $this->assertTrue($filtered->current()->isDir());
    }

    /**
     * @test
     */
    public function canBeFilteredWithSFFiltersByFiletypeFile()
    {
        $this->fs->put('a/aa', 'zzz');

        $it = new RecursiveIterator($this->fs);

        $filtered = $this->filtered(
            $it,
            Finder\Iterator\FileTypeFilterIterator::class,
            Finder\Iterator\FileTypeFilterIterator::ONLY_FILES
        );

        $this->assertCount(1, iterator_to_array($filtered));
        $filtered->rewind();
        $this->assertTrue($filtered->current()->isFile());
    }

    /**
     * @test
     */
    public function canBeFilteredWithSFFiltersBySize()
    {
        $this->fs->put('a/aa', 'zzz');
        $it = new RecursiveIterator($this->fs);

        $greater = $this->justFiles($this->filtered(
            $it,
            Finder\Iterator\SizeRangeFilterIterator::class,
            [new Finder\Comparator\NumberComparator('> 1')]
        ));

        $this->assertCount(1, iterator_to_array($greater));

        $less = $this->justFiles($this->filtered(
            $it,
            Finder\Iterator\SizeRangeFilterIterator::class,
            [new Finder\Comparator\NumberComparator('< 2')]
        ));

        $this->assertCount(0, iterator_to_array($less));

        $equal = $this->justFiles($this->filtered(
            $it,
            Finder\Iterator\SizeRangeFilterIterator::class,
            [new Finder\Comparator\NumberComparator('3')]
        ));

        $this->assertCount(1, iterator_to_array($equal));
    }

    /**
     * @test
     */
    public function canBeFilteredWithSFFiltersByContent()
    {
        $this->fs->put('a/b/c', 'zabcd');
        $this->fs->put('a/d', 'zzzzz');

        $it = new RecursiveIterator($this->fs);

        $filtered = $this->justFiles($this->filtered(
            $it,
            Finder\Iterator\FilecontentFilterIterator::class,
            ['/abc/'],
            []
        ));

        $this->assertCount(1, iterator_to_array($filtered));
    }
    // daterange
    // depthrange
    // excludedirectory
    // sortable
}
