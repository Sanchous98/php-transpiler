<?php

namespace ReCompiler\Tests;

use PHPUnit\Framework\TestCase;
use ReCompiler\PollyFills\WrappedReflectionClass;
use ReCompiler\Tests\Resources\Other;

class PollyFillsTest extends TestCase
{
    public function testWrappedReflectionClass()
    {
        $reflection = new WrappedReflectionClass(Other::class);
        $reflection->getAttributes();
    }
}