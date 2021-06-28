<?php

namespace ReCompiler;

use Exception;
use ReCompiler\Traversers\PHP73Traverser;
use ReCompiler\Traversers\PHP74Traverser;
abstract class TraverserFactory
{
    const PHP80 = "8.0";
    const PHP74 = "7.4";
    const PHP73 = "7.3";
    /**
     * @param  int  $majorVersion
     * @param  int  $minor
     * @return AbstractTraverser
     * @throws Exception
     *
     * @psalm-param positive-int $majorVersion
     */
    public static function factory(int $majorVersion, int $minor = 0) : AbstractTraverser
    {
        switch ("{$majorVersion}.{$minor}") {
            case self::PHP74:
                return new PHP74Traverser();
            case self::PHP73:
                return new PHP73Traverser();
        }
        throw new Exception("Unknown PHP Version");
    }
}