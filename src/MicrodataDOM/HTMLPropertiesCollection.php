<?php

namespace MicrodataDOM;

class HTMLPropertiesCollection implements \ArrayAccess, \IteratorAggregate
{
    protected $element;
    protected $items;
    protected $names;

    public function __construct(DOMElement $element)
    {
        $this->element = $element;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->loadItems());
    }

    public function __get($name)
    {
        switch ($name) {
            case 'names':
                if (null === $this->names) {
                    $names = [];

                    foreach ($this->loadItems() as $item) {
                        foreach ($item->itemProp as $itemProp) {
                            $names[] = $itemProp;
                        }
                    }

                    $this->names = array_values(array_unique($names));
                }

                return $this->names;
            case 'length':
                return count($this->loadItems());
            default:
                throw new \LogicException('Undefined property.');
        }
    }

    public function toArray()
    {
        $result = [];

        foreach ($this as $property) {
            if ($property->itemValue instanceof DOMElement) {
                $itemValue = $property->itemValue->toArray();
            } else {
                $itemValue = $property->itemValue;
            }

            foreach ($property->itemProp as $itemProp) {
                $result[$itemProp][] = $itemValue;
            }
        }

        return $result;
    }

    /**
     * Alias of `getItem`.
     *
     * @see getItem
     */
    public function item($index)
    {
        return $this->getItem($index);
    }

    /**
     * Retrieve a specific element which is defining a property.
     *
     * @param integer $index
     * @return DOMElement
     */
    public function getItem($index)
    {
        $items = $this->loadItems();
        
        if (!isset($items[$index])) {
            throw new \OutOfRangeException();
        }

        return $items[$index];
    }

    /**
     * Alias of `getNamedItem`
     *
     * @see getNamedItem
     */
    public function namedItem($name)
    {
        return $this->getNamedItem($name);
    }

    /**
     * Retrieve a list of elements which define a specific property.
     *
     * @param string $name
     * @return PropertyNodeList
     */
    public function getNamedItem($name)
    {
        return new PropertyNodeList(
            array_values(
                array_filter(
                    $this->loadItems(),
                    function ($element) use ($name) {
                        return in_array($name, $element->itemProp);
                    }
                )
            )
        );
    }

    /**
     * @see \ArrayAccess
     */

    public function offsetGet($offset)
    {
        if (is_string($offset)) {
            return $this->getNamedItem($offset);
        } elseif (is_int($offset)) {
            return $this->getItem($offset);
        }

        throw new \UnexpectedValueException('Expected a string or integer.');
    }

    public function offsetExists($offset)
    {
        if (is_string($offset)) {
            return 0 < $this->getNamedItem($offset)->length;
        } elseif (is_int($offset)) {
            try {
                return null !== $this->getItem($offset);
            } catch (\OutOfRangeException $e) {
                return false;
            }
        }

        throw new \UnexpectedValueException('Expected a string or integer.');
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException();
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException();
    }

    /**
     * self
     */

    protected function loadItems()
    {
        if (null === $this->items) {
            $this->items = $this->recurse($this->element, true);
        }

        return $this->items;
    }

    protected function recurse(DOMElement $element, $root = false)
    {
        $results = [];

        if ($root && $element->itemRef) {
            foreach ($element->itemRef as $itemRef) {
                $item = $this->element->ownerDocument->getElementById($itemRef);

                if ($item) {
                    foreach ($this->recurse($item) as $subresult) {
                        $results[] = $subresult;
                    }
                }
            }
        }

        if (!$root && $element->itemProp) {
            $results[] = $element;
        }

        if ($root || !$element->itemScope) {
            foreach ($element->xpathQuery('/*') as $subelement) {
                foreach ($this->recurse($subelement) as $subresult) {
                    $results[] = $subresult;
                }
            }
        }

        return $results;
    }
}
