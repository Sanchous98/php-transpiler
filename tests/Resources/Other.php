<?php

namespace ReCompiler\Tests\Resources;

/**
 * @Annot(target="class")
 */
#[\Annot(target: "class")]
class Other implements \Serializable
{
    public function concat(iterable $input)
    {
        // TODO: Implement concat() method.
    }
    public function __serialize() : array
    {
        return $this->concat();
    }
    public function __unserialize(array $data)
    {
    }
    public function __sleep()
    {
        return $this->__serialize();
    }
    public function serialize()
    {
        return serialize($this->__serialize());
    }
    public function unserialize($data)
    {
        $this->__unserialize(unserialize($data));
    }
}