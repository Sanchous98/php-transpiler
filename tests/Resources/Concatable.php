<?php

namespace ReCompiler\Tests\Resources;

use Iterator;
interface Concatable
{
    // Modify to iterable
    public function concat(iterable $input);
}