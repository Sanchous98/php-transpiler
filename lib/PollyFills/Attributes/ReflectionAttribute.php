<?php

namespace ReCompiler\PollyFills\Attributes;

use ReflectionClass;
use ReflectionException;

/**
 * @psalm-suppress DuplicateClass
 */
class ReflectionAttribute
{
    public const IS_INSTANCEOF = 2;
    /** @var string */
    protected $name;
    /** @var int */
    protected $target;
    /** @var bool */
    protected $isRepeated;
    /** @var array<string, mixed> */
    protected $arguments;
    /** @var object */
    protected $instance;

    private function __construct()
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTarget(): int
    {
        return $this->target;
    }

    public function isRepeated(): bool
    {
        return $this->isRepeated;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @throws ReflectionException
     */
    public function newInstance(): object
    {
        $reflect = new ReflectionClass($this->instance);
        $new = $reflect->newInstance();
        $newReflect = new ReflectionClass($reflect->newInstance());

        foreach ($this->arguments as $property => $value) {
            $newReflect->getProperty($property)->setValue($value);
        }

        return $new;
    }

    private function __clone()
    {
    }
}
