<?php

namespace MicrodataDOM;

class PropertyNodeList implements \ArrayAccess, \IteratorAggregate
{
    protected $nodes;

    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->nodes);
    }

    public function getValues()
    {
        return array_map(
            function (DOMElement $element) {
                return $element->itemValue;
            },
            $this->nodes
        );
    }

    public function __get($name) {
        switch ($name) {
            case 'length':
                return count($this->nodes);
            default:
                throw new \LogicException('Undefined property');
        }
    }

    /**
     * @see \ArrayAccess
     */

    public function offsetGet($offset)
    {
        if (!isset($this->nodes[$offset])) {
            throw new \OutOfRangeException();
        }

        return $this->nodes[$offset];
    }

    public function offsetExists($offset)
    {
        return isset($this->nodes[$offset]);
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException();
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException();
    }
}
