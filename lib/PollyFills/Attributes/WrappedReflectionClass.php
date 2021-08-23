<?php

namespace ReCompiler\PollyFills\Attributes;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use ReflectionClass;

class WrappedReflectionClass extends ReflectionClass
{
    /**
     * Returns an array of function attributes.
     *
     * @param string|null $name Name of an attribute class
     * @param int $flags Сriteria by which the attribute is searched.
     * @return \ReflectionAttribute[]|ReflectionAttribute[]
     */
    public function getAttributes(?string $name = null, int $flags = 0): array
    {
        $annotations = [];

        if (PHP_VERSION >= 80000) {
            $annotations = parent::getAttributes($name, $flags);
        }

        // Deprecated and will be removed in 2.0 but currently needed
        AnnotationRegistry::registerLoader('class_exists');
        $reader = new AnnotationReader();
        $annotations = array_merge($annotations, isset($name) ? [$reader->getClassAnnotation($this, $name)] : $reader->getClassAnnotations($this));

        foreach ($annotations as &$annotation) {
            if (PHP_VERSION >= 80000 && $annotation instanceof \ReflectionAttribute) {
                continue;
            }
            $obj = $annotation;
            $annotation = new self(ReflectionAttribute::class);
            $annotation->getProperty("instance")->setValue($obj);
            $annotation->getProperty("isRepeated")->setValue(true);
            $annotation->getProperty("name")->setValue(get_class($annotation));

            if (null !== ($target = $reader->getClassAnnotation(new ReflectionClass($annotation), "Target"))) {
                $annotation->getProperty("target")->setValue($target->targets);
            } else {
                $annotation->getProperty("target")->setValue(null);
            }

            $annotation->getProperty("arguments")->setValue(get_object_vars($annotation));
        }

        return $annotations;
    }
}
