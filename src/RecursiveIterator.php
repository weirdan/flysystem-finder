<?php
namespace Weirdan\FlysystemFinder;

use Countable;
use ArrayIterator;
use \RecursiveIterator as SPLRecursiveInterface;
use League\Flysystem\Filesystem;

/**
 * Recursive iterator over the abstract Filesystem
 */
class RecursiveIterator implements SPLRecursiveInterface, Countable
{
    /**
     * @var Filesystem
     */
    protected $fs = null;

    /**
     * @var string
     */
    protected $root = '/';

    /**
     * @var ArrayIterator
     */
    protected $list = null;

    public function __construct(Filesystem $fs, $path = '/')
    {
        $this->fs = $fs;
        $this->root = $path;
    }

    protected function load()
    {
        if ($this->list) {
            return;
        }
        $dirContents = $this->fs->listContents($this->root, false);
        $list = [];
        foreach ($dirContents as $entry) {
            $list[$entry['path']] = $this->fs->get($entry['path']);
        }
        $this->list = new ArrayIterator($list);
    }
    /**
     * Does current element have any children?
     * @return bool
     */
    public function hasChildren()
    {
        $this->load();
        $current = $this->list->current();
        if ($current->isFile()) {
            return false;
        }
        return count($current->getContents()) > 0;
    }

    /**
     * Returns subiterator for children of current element
     * @return self
     */
    public function getChildren()
    {
        $this->load();
        $current = $this->list->current();
        if ($current->isFile()) {
            return false;
        }
        return new static($this->fs, $current->getPath());
    }

    /**
     * Returns current element
     * @return mixed
     */
    public function current()
    {
        $this->load();
        return $this->list->current();
    }

    /**
     * Advances iteration to the next element
     * @return mixed
     */
    public function next()
    {
        $this->load();
        return $this->list->next();
    }

    /**
     * Returns current key
     * @return mixed
     */
    public function key()
    {
        $this->load();
        return $this->list->key();
    }

    /**
     * Aren't we finished yet?
     * @return bool
     */
    public function valid()
    {
        $this->load();
        return $this->list->valid();
    }

    /**
     * Rewinds the iterator
     * @return mixed
     */
    public function rewind()
    {
        $this->load();
        return $this->list->rewind();
    }

    public function count()
    {
        $this->load();
        return count($this->list);
    }
}
