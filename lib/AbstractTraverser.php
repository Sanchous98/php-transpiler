<?php

namespace ReCompiler;

use PhpParser\NodeVisitorAbstract;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
abstract class AbstractTraverser extends NodeVisitorAbstract
{
    const TO_STRING_METHOD = "__toString";
    const PHP_VERSION = "8.1";
}