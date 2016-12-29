<?php
namespace Weirdan\FlysystemFinder;

use League\Flysystem;

class HandlerFileinfoAdapter
{
    /**
     * @var Flysystem\Handler
     */
    protected $handler = null;
    public function __construct(Flysystem\Handler $handler)
    {
        $this->handler = $handler;
    }

    public function getFilename()
    {
        return basename($this->handler->getPath());
    }

    public function getRelativePathname()
    {
        return $this->handler->getPath();
    }

    public function isReadable()
    {
        return true;
    }

    public function getContents()
    {
        if ($this->handler->isFile()) {
            return $this->handler->read();
        }
        return false;
    }

    public function getDirContents()
    {
        if (!$this->handler->isDir()) {
            return false;
        }
        return $this->handler->getContents();
    }

    public function __call($method, $args)
    {
        return [$this->handler, $method](...$args);
    }
}
