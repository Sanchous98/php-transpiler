<?php

namespace ReCompiler\PollyFills;

class ReflectionAttribute
{
    public const IS_INSTANCEOF = 2;
    protected $name;
    protected $target;
    protected $isRepeated;
    protected $arguments;

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

    public function newInstance(): ReflectionAttribute
    {
    }

    private function __clone()
    {
    }
}