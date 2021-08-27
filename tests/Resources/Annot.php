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
    #[\ReCompiler\Tests\Resources\Annot(0)]
    public function __construct(int $target)
    {
    }
}