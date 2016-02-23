<?php

namespace Salexandru\Command;

class Context implements \ArrayAccess
{

    private $data = [];

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->data[$offset];
        }

        return null;
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->offsetGet($key);
        }

        return $default;
    }

    public function set($key, $value, $overwrite = true)
    {
        if (!$overwrite && $this->offsetExists($key)) {
            return;
        }

        $this->offsetSet($key, $value);
    }
}
