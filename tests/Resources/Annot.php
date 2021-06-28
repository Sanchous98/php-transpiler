<?php

namespace ReCompiler\Tests\Resources;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("ALL")
 */
class Annot
{
    public $target;

    #[Annot(0)]
    public function __construct(int $target)
    {
    }
}