<?php

use ReCompiler\PollyFills as p;
if (!class_exists("WeakReferences")) {
    final class WeakReferences extends p\WeakReference
    {
    }
}
if (!class_exists("ReflectionAttribute")) {
    final class ReflectionAttribute extends p\Attributes\ReflectionAttribute
    {
    }
}
if (!class_exists("ReflectionFiber")) {
    final class ReflectionFiber extends p\Fibers\ReflectionFiber
    {
    }
}