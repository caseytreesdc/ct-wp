<?php

namespace NMC_Social;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use ArrayIterator;

class BaseCollection implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Items
     *
     * @var array
     */
    protected $items = [];

    /**
     * Add to the Collection
     *
     * @param [type] $item [description]
     */
    public function add($item)
    {
        $this->items[] = $item;
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }

    /**
     * Count the number of items
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Get Iterator
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }


}