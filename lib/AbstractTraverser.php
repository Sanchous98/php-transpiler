<?php

namespace ReCompiler;

use PhpParser\NodeVisitorAbstract;
abstract class AbstractTraverser extends NodeVisitorAbstract
{
    const TO_STRING_METHOD = "__toString";
    const PHP_VERSION = "8.1";
}