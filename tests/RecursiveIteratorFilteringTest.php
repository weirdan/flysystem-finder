<?php
namespace Weirdan\FlysystemFinder\Tests;

use Iterator;
use RecursiveIteratorIterator;
use PHPUnit_Framework_TestCase as PHPUnit;
use League\Flysystem\Adapter;
use League\Flysystem\Filesystem;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Finder;
use Weirdan\FlysystemFinder\RecursiveIterator;

class RecursiveIteratorFilteringTest extends PHPUnit
{
    public function setUp()
    {
        $this->root = vfsStream::setup('testroot');
        $this->fs = new Filesystem(new Adapter\Local(vfsStream::url('testroot'), 0));
    }

    /**
     * @test
     */
    public function canBeFilteredByFilename()
    {
        $this->fs->put('a/aa', 'aa');
        $this->fs->put('a/bb', 'bb');

        $it = new RecursiveIterator($this->fs);

        $filtered = $this->filtered($it, Finder\Iterator\FilenameFilterIterator::class, ['bb'], []);
        $this->assertCount(1, iterator_to_array($filtered));
    }


    /**
     * @test
     */
    public function canBeFilteredByPathname()
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
    public function canBeFilteredByFiletypeDir()
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
    public function canBeFilteredByFiletypeFile()
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
    public function canBeFilteredBySize()
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
    public function canBeFilteredByContent()
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

    protected function filtered(RecursiveIterator $it, string $by, ...$args)
    {
        return new $by(new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST), ...$args);
    }

    protected function justFiles(Iterator $it)
    {
        return new Finder\Iterator\FileTypeFilterIterator($it, Finder\Iterator\FileTypeFilterIterator::ONLY_FILES);
    }
    // daterange
    // depthrange
    // excludedirectory
}
