<?php

namespace ReCompiler\PollyFills\Fibers;

use Generator;
use Throwable;

/**
 * @psalm-suppress DuplicateClass
 */
class Fiber
{
    /**
     * @var Generator
     */
    private $generator;
    /**
     * @psalm-var callable(): Generator
     */
    private $callback;
    private $running = false;
    private $started = false;
    private $terminated = false;

    /**
     * @param callable $callback Function to invoke when starting the fiber.
     * @psalm-param callable(): Generator
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Starts execution of the fiber. Returns when the fiber suspends or terminates.
     *
     * @param mixed ...$args Arguments passed to fiber function.
     *
     * @return mixed Value from the first suspension point or NULL if the fiber returns.
     *
     * @throw FiberError If the fiber has already been started.
     * @throw Throwable If the fiber callable throws an uncaught exception.
     */
    public function start(...$args)
    {
        $this->checkIsStarted();
        $this->started = $this->running = true;

        if (!isset($this->generator)) {
            $this->generator = call_user_func_array($this->callback, $args);

            if (!$this->generator instanceof Generator) {
                throw new \RuntimeException("Callback must be a generator");
            }
        }

        $this->running = false;

        if ($this->generator->valid()) {
            $this->generator->next();

            return $this->generator->current();
        }

        $this->terminated = true;

        return $this->generator->getReturn();
    }

    /**
     * Resumes the fiber, returning the given value from {@see Fiber::suspend()}.
     * Returns when the fiber suspends or terminates.
     *
     * @param mixed $value
     *
     * @return mixed Value from the next suspension point or NULL if the fiber returns.
     *
     * @throw FiberError If the fiber has not started, is running, or has terminated.
     * @throw Throwable If the fiber callable throws an uncaught exception.
     */
    public function resume($value = null)
    {
        $this->checkIsNotStarted();
        $this->checkIsRunning();
        $this->checkIsTerminated();

        $this->running = true;
        $this->generator->send($value);
        $this->running = false;

        if ($this->generator->valid()) {
            return $this->generator->current();
        }

        $this->terminated = true;

        return $this->getReturn();
    }

    /**
     * Throws the given exception into the fiber from {@see Fiber::suspend()}.
     * Returns when the fiber suspends or terminates.
     *
     * @param Throwable $exception
     *
     * @return mixed Value from the next suspension point or NULL if the fiber returns.
     *
     * @throw FiberError If the fiber has not started, is running, or has terminated.
     * @throw Throwable If the fiber callable throws an uncaught exception.
     */
    public function throw(Throwable $exception)
    {
        $this->checkIsNotStarted();
        $this->checkIsRunning();
        $this->checkIsNotTerminated();

        if (!$this->isRunning()) {
            throw new FiberError("Fiber is suspended");
        }

        if (!$this->isTerminated()) {
            throw new FiberError("Fiber is already terminated");
        }

        $this->generator->throw($exception);
        $this->running = false;

        if ($this->generator->valid()) {
            return $this->generator->current();
        }

        $this->terminated = true;

        return $this->getReturn();
    }

    /**
     * @return bool True if the fiber has been started.
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * @return bool True if the fiber is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->started && !$this->running;
    }

    /**
     * @return bool True if the fiber is currently running.
     */
    public function isRunning(): bool
    {
        return $this->started && $this->running;
    }

    /**
     * @return bool True if the fiber has completed execution (returned or threw).
     */
    public function isTerminated(): bool
    {
        return $this->terminated;
    }

    /**
     * @return mixed Return value of the fiber callback. NULL is returned if the fiber does not have a return statement.
     *
     * @throws FiberError If the fiber has not terminated or the fiber threw an exception.
     */
    public function getReturn()
    {
        $this->checkIsNotTerminated();

        return $this->generator->getReturn();
    }

    /**
     * Suspend execution of the fiber. The fiber may be resumed with {@see Fiber::resume()} or {@see Fiber::throw()}.
     *
     * Cannot be called from {main}.
     *
     * @param mixed $value Value to return from {@see Fiber::resume()} or {@see Fiber::throw()}.
     *
     * @return mixed Value provided to {@see Fiber::resume()}.
     *
     * @throws FiberError Thrown if not within a fiber (i.e., if called from {main}).
     * @throws Throwable Exception provided to {@see Fiber::throw()}.
     *
     * @deprecated Cannot be implemented in PHP <8.1. Will be replaced by yields
     */
    public static function suspend($value = null)
    {
        return null;
    }

    /**
     * @return self|null Returns the currently executing fiber instance or NULL if in {main}.
     *
     * @deprecated Cannot be implemented in PHP <8.1. Usage will cause an exception. Try to use concrete variable
     *             instead
     */
    public static function this()
    {
        return null;
    }

    /**
     * @throws FiberError
     */
    private function checkIsRunning(): void
    {
        if ($this->isRunning()) {
            throw new FiberError("Fiber is already running");
        }
    }

    /**
     * @throws FiberError
     */
    private function checkIsNotStarted(): void
    {
        if (!$this->isStarted()) {
            throw new FiberError("Fiber is not yet running");
        }
    }

    /**
     * @throws FiberError
     */
    private function checkIsStarted(): void
    {
        if ($this->isStarted()) {
            throw new FiberError("Fiber is already started");
        }
    }

    /**
     * @throws FiberError
     */
    private function checkIsNotTerminated(): void
    {
        if (!$this->isTerminated()) {
            throw new FiberError("Fiber is not yet terminated");
        }
    }

    /**
     * @throws FiberError
     */
    private function checkIsTerminated(): void
    {
        if (!$this->isTerminated()) {
            throw new FiberError("Fiber is already terminated");
        }
    }
}
