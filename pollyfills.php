<?php

use ReCompiler\PollyFills as p;
if (!class_exists("WeakReferences")) {
    final class WeakReferences extends p\WeakReference
    {
    }
}
if (!class_exists("ReflectionAttribute")) {
    final class ReflectionAttribute extends p\ReflectionAttribute
    {
    }
}
