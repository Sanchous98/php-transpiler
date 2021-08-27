<?php

namespace ReCompiler;

use Exception;
use ReCompiler\Visitors\PHP73Visitor;
use ReCompiler\Visitors\PHP74Visitor;
use ReCompiler\Visitors\PHP80Visitor;
abstract class VisitorFactory
{
    public const PHP80 = "8.0";
    public const PHP74 = "7.4";
    public const PHP73 = "7.3";
    /**
     * @psalm-param positive-int $majorVersion
     * @psalm-param positive-int|0 $minor
     * @throws Exception
     */
    public static function factory(int $majorVersion, int $minor = 0) : AbstractTraverser
    {
        switch ("{$majorVersion}.{$minor}") {
            case self::PHP80:
                return new PHP80Visitor();
            case self::PHP74:
                return new PHP74Visitor();
            case self::PHP73:
                return new PHP73Visitor();
        }
        throw new Exception("Unknown PHP Version");
    }
}