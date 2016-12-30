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

    public function getRealpath()
    {
        // there's no "real" path on an abstract fs
        return $this->getPath();
    }

    public function getATime()
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function getCTime()
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function getMTime()
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->handler, $method], $args);
    }
}
