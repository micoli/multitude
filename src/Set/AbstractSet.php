<?php

declare(strict_types=1);

namespace Micoli\Multitude\Set;

use Countable;
use Generator;
use IteratorAggregate;
use Micoli\Multitude\Exception\EmptySetException;
use Micoli\Multitude\Exception\LogicException;
use Micoli\Multitude\Exception\NotFoundException;
use Micoli\Multitude\Exception\ValueAlreadyPresentException;
use Micoli\Multitude\ImmutableInterface;
use Micoli\Multitude\MutableInterface;
use Traversable;

/**
 * @template TValue
 *
 * @implements IteratorAggregate<int, TValue>
 */
class AbstractSet implements Countable, IteratorAggregate
{
    /**
     * @var list<TValue>
     */
    protected array $values;
    private bool $isMutable;

    /**
     * @param list<TValue> $values
     */
    protected function __construct(array $values = [])
    {
        if (!($this instanceof MutableInterface) && !($this instanceof ImmutableInterface)) {
            throw new LogicException('Map must be either Mutable or Immutable');
        }
        if (($this instanceof MutableInterface) == ($this instanceof ImmutableInterface)) {
            throw new LogicException('Map must be either Mutable or Immutable');
        }
        $this->isMutable = $this instanceof MutableInterface;
        $this->values = $values;
    }

    /**
     * @template TV
     *
     * @param iterable<TV> $values
     *
     * @return static<TV>
     */
    public static function fromArray(iterable $values): static
    {
        $buffer = [];
        foreach ($values as $value) {
            if (in_array($value, $buffer, true)) {
                continue;
            }
            $buffer[] = $value;
        }
        /** @psalm-suppress UnsafeInstantiation */
        return new static($buffer);
    }

    public function count(): int
    {
        return count($this->values);
    }

    private function getInstance(): static
    {
        return $this->isMutable ? $this : clone ($this);
    }

    /**
     * @param TValue $newValue
     *
     * @return static<TValue>
     */
    public function append(mixed $newValue, bool $throw = true): static
    {
        $instance = $this->getInstance();
        foreach ($instance->values as $value) {
            if ($value === $newValue) {
                if ($throw) {
                    throw new ValueAlreadyPresentException(sprintf('Value %s already present', $newValue));
                }

                return $this;
            }
        }
        $instance->values[] = $newValue;

        return $instance;
    }

    /**
     * @param TValue $searchedValue
     */
    public function remove(mixed $searchedValue, bool $throw = true): static
    {
        $instance = $this->getInstance();
        $found = false;
        for ($index = count($instance->values); $index >= 0; --$index) {
            if ($instance->values[$index] === $searchedValue) {
                unset($instance->values[$index]);
                $found = true;
            }
        }
        if ($found) {
            $instance->values = array_values($instance->values);

            return $instance;
        }
        if (!$throw) {
            return $this;
        }
        if (is_object($searchedValue)) {
            throw new NotFoundException(sprintf('Value object #%s not found', spl_object_id($searchedValue)));
        }
        throw new NotFoundException(sprintf('Value %s not found', $searchedValue));
    }

    /**
     * @param TValue $searchedValue
     *
     * @return int<-1, max>
     */
    private function indexOf(mixed $searchedValue): int
    {
        foreach ($this->values as $index => $value) {
            if ($searchedValue === $value) {
                return $index;
            }
        }

        return -1;
    }

    /**
     * @param TValue $searchedValue
     */
    public function hasValue(mixed $searchedValue): bool
    {
        return $this->indexOf($searchedValue) >= 0;
    }

    public function isEmpty(): bool
    {
        return count($this->values) === 0;
    }

    public function getIterator(): Traversable
    {
        foreach ($this->values as $value) {
            yield $value;
        }
    }

    /**
     * @return list<TValue>
     */
    public function toArray(): array
    {
        return $this->values;
    }

    /**
     * @return Generator<int>
     */
    public function keys(): Generator
    {
        foreach ($this->values as $key => $values) {
            yield $key;
        }
    }

    /**
     * @return Generator<TValue>
     */
    public function values(): Generator
    {
        foreach ($this->values as $value) {
            yield $value;
        }
    }

    /**
     * @return TValue
     */
    public function first(bool $throw = true): mixed
    {
        if (count($this->values) > 0) {
            reset($this->values);

            return current($this->values);
        }
        if ($throw) {
            throw new EmptySetException('Empty set when calling first');
        }

        return null;
    }

    /**
     * @return TValue
     */
    public function last(bool $throw = true): mixed
    {
        if (count($this->values) > 0) {
            end($this->values);

            return current($this->values);
        }
        if ($throw) {
            throw new EmptySetException('Empty set when calling last');
        }

        return null;
    }

    /**
     * @template TResult
     *
     * @param callable(TValue, int):TResult $callable
     *
     * @return static<TResult>
     *
     * @psalm-suppress  InvalidArgument
     */
    public function map(callable $callable): static
    {
        /** @var static<TResult> $instance */
        $instance = $this->getInstance();
        $instance->values = array_map(fn (mixed $value, mixed $key) => $callable($value, $key), $instance->values, array_keys($instance->values));

        return $instance;
    }

    /**
     * @template TResult
     * @template TAccumulator
     *
     * @param callable(TAccumulator, TValue, int):TAccumulator $callable
     * @param TAccumulator $accumulator
     *
     * @return TAccumulator
     *
     * @psalm-suppress  InvalidArgument
     */
    public function reduce(callable $callable, mixed $accumulator): mixed
    {
        foreach ($this->values as $index => $value) {
            $accumulator = $callable($accumulator, $value, $index);
        }

        return $accumulator;
    }

    /**
     * @param callable(TValue, int):bool $callable
     *
     * @return static<TValue>
     *
     * @psalm-suppress  InvalidArgument
     */
    public function filter(callable $callable): static
    {
        /** @var static<TValue> $instance */
        $instance = $this->getInstance();
        $values = $this->values;

        /** @var list<TValue> $instance->values */
        $instance->values = [];
        foreach ($values as $index => $value) {
            if (!$callable($value, $index)) {
                continue;
            }
            $instance->values[] = $value;
        }

        return $instance;
    }
}
