<?php

namespace ReCompiler\Tests;

use PHPUnit\Framework\TestCase;
use ReCompiler\PollyFills\WrappedReflectionClass;
use ReCompiler\Tests\Resources\Other;
class PollyFillsTest extends \PHPUnit\Framework\TestCase
{
    public function testWrappedReflectionClass()
    {
        $reflection = new \ReCompiler\PollyFills\WrappedReflectionClass(\ReCompiler\Tests\Resources\Other::class);
        $reflection->getAttributes();
    }
}