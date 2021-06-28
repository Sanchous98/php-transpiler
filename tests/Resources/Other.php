<?php

namespace ReCompiler\Tests\Resources;

use Iterator;

/**
 * @Annot(target="class")
 */
#[\Annot(target: "class")]
class Other implements Concatable
{
    public function concat(iterable $input)
    {
        // TODO: Implement concat() method.
    }
}