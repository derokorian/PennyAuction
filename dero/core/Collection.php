<?php

namespace Dero\Core;


class Collection implements \Iterator,
                            \Countable
{
    protected $items = [];
    protected $index = 0;

    /**
     * Adds an item to the collection
     * @param mixed $item
     */
    public function add($item)
    {
        $this->items[] = $item;
    }

    /**
     * (non-PHPDoc)
     * @see Iterator::current()
     */
    public function current ()
    {
        return $this->items[$this->index];
    }

    /**
     * (non-PHPDoc)
     * @see Iterator::key()
     */
    public function key ()
    {
        return $this->index;
    }

    /**
     * (non-PHPDoc)
     * @see Iterator::next()
     */
    public function next ()
    {
        ++$this->index;
    }

    /**
     * (non-PHPDoc)
     * @see Iterator::rewind()
     */
    public function rewind ()
    {
        $this->Index = 0;
    }

    /**
     * (non-PHPDoc)
     * @see Iterator::valid()
     */
    public function valid ()
    {
        return isset($this->items[$this->index]);
    }

    /**
     * (non-PHPDoc)
     * @see Countable::count()
     */
    public function count ()
    {
        return count($this->items);
    }
} 