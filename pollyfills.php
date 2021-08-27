<?php

use ReCompiler\PollyFills as p;
if (!\class_exists("WeakReferences")) {
    final class WeakReferences extends \ReCompiler\PollyFills\WeakReference
    {
    }
}
if (!\class_exists("ReflectionAttribute")) {
    final class ReflectionAttribute extends \ReCompiler\PollyFills\Attributes\ReflectionAttribute
    {
    }
}
if (!\class_exists("ReflectionFiber")) {
    final class ReflectionFiber extends \ReCompiler\PollyFills\Fibers\ReflectionFiber
    {
    }
}
if (!\class_exists("Fiber")) {
    final class Fiber extends \ReCompiler\PollyFills\Fibers\Fiber
    {
    }
}