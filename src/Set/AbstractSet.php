<?php

declare(strict_types=1);

namespace Micoli\Multitude\Set;

use Countable;
use Generator;
use IteratorAggregate;
use Micoli\Multitude\AbstractMultitude;
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
class AbstractSet extends AbstractMultitude implements Countable, IteratorAggregate
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
     * Return a new instance from an array. dedup values on construction
     *
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

    /**
     * return the number of items in the set
     */
    public function count(): int
    {
        return count($this->values);
    }

    private function getInstance(): static
    {
        return $this->isMutable ? $this : clone ($this);
    }

    /**
     * Append a value at the end of the set
     *
     * Throw a ValueAlreadyPresentException if value is already present in the set and $throw==true
     *
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
     * Remove a value in the set
     *
     * Throw a NotFoundException if value is not found and $throw==true
     *
     * @param TValue $searchedValue
     */
    public function remove(mixed $searchedValue, bool $throw = true): static
    {
        $instance = $this->getInstance();
        $found = false;
        for ($index = count($instance->values) - 1; $index >= 0; --$index) {
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
     * Return if a set contains a value
     *
     * @param TValue $searchedValue
     */
    public function hasValue(mixed $searchedValue): bool
    {
        return $this->indexOf($searchedValue) >= 0;
    }

    /**
     * Return if a set is empty
     */
    public function isEmpty(): bool
    {
        return count($this->values) === 0;
    }

    /**
     * Return an iterator for values
     */
    public function getIterator(): Traversable
    {
        foreach ($this->values as $value) {
            yield $value;
        }
    }

    /**
     * Return an array representing the values
     *
     * @return list<TValue>
     */
    public function toArray(): array
    {
        return $this->values;
    }

    /**
     * Return a value in the set by index
     *
     * if index is not found, default value is returned
     *
     * @param int $index
     * @param ?TValue $defaultValue
     *
     * @return TValue
     */
    public function get(mixed $index, mixed $defaultValue = null): mixed
    {
        if (array_key_exists($index, $this->values)) {
            return $this->values[$index];
        }

        return $defaultValue;
    }

    /**
     * Return an iterator of keys
     *
     * @return Generator<int>
     */
    public function keys(): Generator
    {
        foreach ($this->values as $key => $values) {
            yield $key;
        }
    }

    /**
     * Return an iterator of values
     *
     * @return Generator<TValue>
     */
    public function values(): Generator
    {
        foreach ($this->values as $value) {
            yield $value;
        }
    }

    /**
     * Return the first value in the set
     *
     * EmptySetException is thrown if set is empty and $throw === true
     *
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
     * Return the latest value in the set
     *
     * EmptySetException is thrown if set is empty and $throw === true
     *
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
     * Applies the callback to the values, keys are preserved
     * Callback receive `$value` and `$index`
     *
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
        $instance->values = array_map(
            /** @return TResult */
            fn (mixed $value, mixed $key): mixed => $callable($value, $key),
            $instance->values,
            array_keys($instance->values),
        );

        return $instance;
    }

    /**
     * Iteratively reduce the Set to a single value using a callback function
     * Callback receive `$accumulator`,`$value` and `$index`
     *
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
     * Filter the set using a callback function
     *
     * Callback receive `$value` and `$index`
     *
     * @param callable(TValue, int):bool $callable
     *
     * @return static<TValue>
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

    /**
     * Extract a slice of the set
     *
     * @return static<TValue>
     */
    public function slice(int $offset, ?int $length = null): static
    {
        $max = $this->getSliceMax(count($this->values), $offset, $length);

        return $this->filter(fn (mixed $value, int $index) => $index >= $offset && $index <= $max);
    }

    /**
     * Apply a callback on set values
     * Callback receive `$value` and `$index`
     *
     * @param callable(TValue, int):bool $callable
     *
     * @return static<TValue>
     */
    public function forEach(callable $callable): static
    {
        foreach ($this->values as $index => $value) {
            if (!$callable($value, $index)) {
                return $this;
            }
        }

        return $this;
    }
}
