<?php
namespace Weirdan\FlysystemFinder;

use BadMethodCallException;

/**
 * Exception to signify not implemented methods
 */
class NotImplementedException extends BadMethodCallException
{
    /**
     * Method name
     * @var string
     */
    protected $method = '';
    /**
     * {@inheritDoc}
     * @param string $method Method that was called but is not currently implemented
     */
    public function __construct($method)
    {
        $this->method = $method;
        parent::__construct("Method '$method' is not implemented");
    }

    /**
     * Returns method that was called
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}
