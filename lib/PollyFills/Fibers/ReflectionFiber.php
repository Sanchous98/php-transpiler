<?php

namespace ReCompiler\PollyFills\Fibers;

use ReflectionException;

/**
 * @psalm-suppress DuplicateClass
 */
class ReflectionFiber
{
    private $fiber;
    private $reflect;

    /**
     * @param Fiber $fiber Any Fiber object, including those that are not started or have
     *                     terminated.
     */
    public function __construct(Fiber $fiber) {
        $this->fiber = $fiber;
        $this->reflect = new \ReflectionClass($fiber);
    }

    /**
     * @return Fiber The reflected Fiber object.
     */
    public function getFiber(): Fiber
    {
        return $this->fiber;
    }

    /**
     * @return string Current file of fiber execution.
     */
    public function getExecutingFile(): string
    {
        return $this->reflect->getFileName();
    }

    /**
     * @return int Current line of fiber execution.
     */
    public function getExecutingLine(): int
    {
        return $this->reflect->getStartLine();
    }

    /**
     * @param int $options Same flags as {@see debug_backtrace()}.
     *
     * @return array Fiber backtrace, similar to {@see debug_backtrace()}
     *               and {@see ReflectionGenerator::getTrace()}.
     */
    public function getTrace(int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT): array
    {
        return debug_backtrace($options);
    }

    /**
     * @return bool True if the fiber has been started.
     * @throws ReflectionException
     */
    public function isStarted(): bool
    {
        return (bool) $this->reflect->getProperty("started")->getValue();
    }

    /**
     * @return bool True if the fiber is currently suspended.
     * @throws ReflectionException
     */
    public function isSuspended(): bool
    {
        return (bool) $this->reflect->getProperty("suspended")->getValue();
    }

    /**
     * @return bool True if the fiber is currently running.
     * @throws ReflectionException
     */
    public function isRunning(): bool
    {
        return (bool) $this->reflect->getProperty("running")->getValue();
    }

    /**
     * @return bool True if the fiber has completed execution (either returning or
     *              throwing an exception), false otherwise.
     * @throws ReflectionException
     */
    public function isTerminated(): bool
    {
        return (bool) $this->reflect->getProperty("terminated")->getValue();
    }
}
