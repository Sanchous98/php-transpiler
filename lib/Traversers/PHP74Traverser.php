<?php

namespace ReCompiler\Traversers;

use PhpParser\Node;
use ReCompiler\AbstractTraverser;
/**
 * @todo Remove Union types
 * @todo Preprocess "null-safe" operators
 * @todo Preprocess named arguments
 * @todo Preprocess Attributes
 * @todo Preprocess "match"
 * @todo Preprocess constructor properties declaration
 * @todo Remove "static" return type
 * @todo Remove "mixed" return and param type
 * @todo Preprocess throwing exceptions in arrow functions and operators
 * @todo Implement WeakMap or announce that they are not supported
 * @todo Replace "::class" by "get_class" function on objects
 * @todo Preprocess "catch" blocks without param
 * @todo Remove tailing comma in functions
 * @todo Find a solution for "::createFromInterface" for DateTime and DateTimeImmutable
 * @todo Replace PhpToken::getAll() by token_get_all() or implement PhpToken class
 * @todo Preprocess or announce about variables syntax unifying
 * @todo Preprocess concatenation operator order
 * @todo Add ext-json to composer.json
 */
class PHP74Traverser extends AbstractTraverser
{
    const PHP_VERSION = "7.4";
    public function enterNode(Node $node) : ?Node
    {
        // TODO: Implement 8.0 -> 7.4 preprocessing
        return $node;
    }
    protected function preprocessAttributes(Node\Attribute $attribute) : void
    {
    }
}