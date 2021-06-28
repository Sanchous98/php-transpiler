<?php

namespace ReCompiler\PollyFills\Attributes;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\DocParser;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use ReCompiler\DocParserFactory;
use ReflectionClass;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

class WrappedReflectionClass extends ReflectionClass
{
    /** {@inheritDoc} */
    public function getAttributes(?string $name = null, int $flags = 0): array
    {
        // Deprecated and will be removed in 2.0 but currently needed
        AnnotationRegistry::registerLoader('class_exists');
        $reader = new AnnotationReader();
        $annotations = isset($name) ? $reader->getClassAnnotation($this, $name) : $reader->getClassAnnotations($this);
//        try {
//        } catch (AnnotationException $e) {
//            $ast = (new DocParserFactory())->tokenize($this->getDocComment());
//            dump($ast);
//        }

        return [];
    }
}
