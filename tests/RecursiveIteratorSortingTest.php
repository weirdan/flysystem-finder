<?php
namespace Weirdan\FlysystemFinder\Tests;

use RecursiveIteratorIterator;
use PHPUnit\Framework\TestCase as PHPUnit;
use League\Flysystem\Adapter;
use League\Flysystem\Filesystem;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Finder\Iterator\SortableIterator;
use Weirdan\FlysystemFinder\RecursiveIterator;
use Weirdan\FlysystemFinder\NotImplementedException;

class RecursiveIteratorSortingTest extends PHPUnit
{
    public function setUp()
    {
        $this->root = vfsStream::setup('testroot');
        $this->fs = new Filesystem(new Adapter\Local(vfsStream::url('testroot'), 0));
    }

    /**
     * @test
     */
    public function canBeSortedByName()
    {
        $this->fs->put('aa/b', '');
        $this->fs->put('aa/a', '');

        $it = new RecursiveIterator($this->fs);
        $sorted = $this->sorted($it, SortableIterator::SORT_BY_NAME);

        $this->assertEquals(
            ['aa', 'aa/a', 'aa/b'],
            array_keys(iterator_to_array($sorted))
        );
    }

    /**
     * @test
     */
    public function canBeSortedByType()
    {
        $this->fs->put('a', '');
        $this->fs->put('b/bb', '');
        $this->fs->put('c', '');
        $this->fs->put('d/dd', '');

        $it = new RecursiveIterator($this->fs);
        $sorted = $this->sorted($it, SortableIterator::SORT_BY_TYPE);

        $this->assertEquals(
            [
                // dirs
                'b', 'd',
                // files in lexicographic order
                'a', 'b/bb', 'c', 'd/dd'
            ],
            array_keys(iterator_to_array($sorted))
        );
    }

    /**
     * @test
     */
    public function cannotBeSortedByAtime()
    {
        $this->fs->put('a', '');
        $this->fs->put('b', '');

        $it = new RecursiveIterator($this->fs);

        $this->expectException(NotImplementedException::class);

        $sorted = $this->sorted($it, SortableIterator::SORT_BY_ACCESSED_TIME);
        foreach ($sorted as $obj) {
            // intentionally empty, just to trigger iteration
        }
    }

    /**
     * @test
     */
    public function cannotBeSortedByCtime()
    {
        $this->fs->put('a', '');
        $this->fs->put('b', '');

        $it = new RecursiveIterator($this->fs);

        $this->expectException(NotImplementedException::class);

        $sorted = $this->sorted($it, SortableIterator::SORT_BY_CHANGED_TIME);
        foreach ($sorted as $obj) {
            // intentionally empty, just to trigger iteration
        }
    }

    /**
     * @test
     */
    public function cannotBeSortedByMtime()
    {
        $this->fs->put('a', '');
        $this->fs->put('b', '');

        $it = new RecursiveIterator($this->fs);

        $this->expectException(NotImplementedException::class);

        $sorted = $this->sorted($it, SortableIterator::SORT_BY_MODIFIED_TIME);
        foreach ($sorted as $obj) {
            // intentionally empty, just to trigger iteration
        }
    }

    protected function sorted(RecursiveIterator $it, $sort)
    {
        $flat = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);
        $sorted = new SortableIterator($flat, $sort);
        return $sorted;
    }

    // sortable
}
