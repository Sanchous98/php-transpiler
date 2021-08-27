<?php

namespace ReCompiler;

use Composer\Autoload\ClassLoader;
use PhpParser\NodeVisitorAbstract;
use ReCompiler\Visitors\CurrentFileTrait;
abstract class AbstractTraverser extends NodeVisitorAbstract
{
    use CurrentFileTrait;
    /** @var ClassLoader */
    protected $loader;
    protected $nameContext;
    public const TO_STRING_METHOD = "__toString";
    public const PHP_VERSION = "8.1";
    public function __construct()
    {
        /** @psalm-suppress MixedAssignment */
        $this->loader = (include 'vendor/autoload.php');
    }
}