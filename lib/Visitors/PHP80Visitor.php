<?php

namespace ReCompiler\Visitors;

use PhpParser\Node;
use ReCompiler\AbstractTraverser;
/**
 * @todo Remove string key array unpacking
 * @todo Replace "never" return type by "void"
 * @todo Remove explicit octal numbers
 * @todo Replace enums by abstract classes
 * @todo Remove Fibers
 * @todo Announce overwriting $GLOBAL statements
 * @todo Announce MYSQLI_STMT_ATTR_UPDATE_MAX_LENGTH usage
 * @todo Announce MYSQLI_STORE_RESULT_COPY_DATA usage
 */
class PHP80Visitor extends AbstractTraverser
{
    public const PHP_VERSION = "8.0";
    public function enterNode(Node $node) : Node
    {
        // TODO: Implement 8.1 -> 8.0 traverser
        return $node;
    }
}