<?php

declare(strict_types=1);

namespace Micoli\Multitude\Map;

use ArrayAccess;
use Countable;
use Generator;
use IteratorAggregate;
use Micoli\Multitude\AbstractMultitude;
use Micoli\Multitude\Exception\EmptySetException;
use Micoli\Multitude\Exception\GenericException;
use Micoli\Multitude\Exception\InvalidArgumentException;
use Micoli\Multitude\Exception\LogicException;
use Micoli\Multitude\Exception\OutOfBoundsException;
use Micoli\Multitude\ImmutableInterface;
use Micoli\Multitude\Map\Operation\Filter;
use Micoli\Multitude\Map\Operation\KeyDiff;
use Micoli\Multitude\Map\Operation\KeyIntersect;
use Micoli\Multitude\Map\Operation\Sort;
use Micoli\Multitude\Map\Operation\ValueDiff;
use Micoli\Multitude\Map\Operation\ValueIntersect;
use Micoli\Multitude\MutableInterface;
use Stringable;
use Traversable;

/**
 * @template TKey
 * @template TValue
 *
 * @template-implements IteratorAggregate<TKey, TValue>
 * @template-implements ArrayAccess<TKey, TValue>
 *
 * @phpstan-consistent-constructor
 *
 * @psalm-consistent-constructor
 *
 * @psalm-consistent-templates
 */
class AbstractMap extends AbstractMultitude implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @var list<array{TKey, TValue}>
     */
    protected array $tuples;
    private bool $isMutable;

    /**
     * @param list<array{TKey, TValue}> $tuples
     *
     * @codeCoverageIgnore
     */
    public function __construct(array $tuples = [])
    {
        if (!($this instanceof MutableInterface) && !($this instanceof ImmutableInterface)) {
            throw new LogicException('Map must be either Mutable or Immutable');
        }
        if (($this instanceof MutableInterface) == ($this instanceof ImmutableInterface)) {
            throw new LogicException('Map must be either Mutable or Immutable');
        }
        $this->isMutable = $this instanceof MutableInterface;
        $this->tuples = $tuples;
    }

    /**
     * Return a new instance from an array.
     *
     * @template TK of TKey
     * @template TV of TValue
     *
     * @param iterable<TK, TV> $values
     *
     * @phan-return static<TK, TV>
     */
    public static function fromIterable(iterable $values): static
    {
        $buffer = [];
        foreach ($values as $key => $value) {
            $buffer[] = [$key, $value];
        }
        /** @psalm-suppress UnsafeInstantiation */
        return new static($buffer);
    }

    /**
     * @return list<array{TKey, TValue}>
     */
    public function getTuples(): array
    {
        return $this->tuples;
    }

    /**
     * Return the number of items in the map
     */
    public function count(): int
    {
        return count($this->tuples);
    }

    private function isImmutable(): bool
    {
        return !$this->isMutable;
    }

    private function getInstance(bool $forceNew = false): static
    {
        if ($forceNew) {
            return clone $this;
        }

        return $this->isMutable ? $this : clone ($this);
    }

    /**
     * Add or replace a value in the map
     *
     * @param TKey $newKey
     * @param TValue $newValue
     */
    public function set(mixed $newKey, mixed $newValue): static
    {
        if ($newKey === null) {
            throw new InvalidArgumentException('Key must not be null');
        }
        $instance = $this->getInstance();
        /**
         * @var TKey $key
         * @var TValue $value
         */
        foreach ($instance->tuples as $index => [$key, $value]) {
            if ($key === $newKey) {
                $instance->tuples[$index] = [$newKey, $newValue];

                return $instance;
            }
        }
        $instance->tuples[] = [$newKey, $newValue];

        return $instance;
    }

    /**
     * Remove a value in the map by key
     *
     * @param TKey $searchedKey
     */
    public function removeKey(mixed $searchedKey): static
    {
        $instance = $this->getInstance();
        $keyIndex = $instance->keyIndex($searchedKey);
        if ($keyIndex !== -1) {
            unset($instance->tuples[$keyIndex]);
            $instance->tuples = array_values($instance->tuples);
        }

        return $instance;
    }

    /**
     * Remove a value in the map by value
     *
     * @param TValue $searchedValue
     */
    public function removeValue(mixed $searchedValue): static
    {
        $instance = $this->getInstance();
        for ($index = count($instance->tuples) - 1; $index >= 0; --$index) {
            if ($instance->tuples[$index][1] === $searchedValue) {
                unset($instance->tuples[$index]);
            }
        }
        $instance->tuples = array_values($instance->tuples);

        return $instance;
    }

    /**
     * @param TKey $searchedKey
     */
    private function keyIndex(mixed $searchedKey): int
    {
        foreach ($this->tuples as $index => [$key, $value]) {
            if ($key === $searchedKey) {
                return $index;
            }
        }

        return -1;
    }

    /**
     * Return if a map contains a specific key
     *
     * @param TKey $searchedKey
     */
    public function hasKey(mixed $searchedKey): bool
    {
        return $this->keyIndex($searchedKey) >= 0;
    }

    /**
     * Return if a map contains a specific value
     *
     * @param TValue $searchedValue
     */
    public function hasValue(mixed $searchedValue): bool
    {
        foreach ($this->tuples as [$key, $value]) {
            if ($value === $searchedValue) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return if a map is empty
     */
    public function isEmpty(): bool
    {
        return count($this->tuples) === 0;
    }

    /**
     * Return an iterator for values by keys
     */
    public function getIterator(): Traversable
    {
        foreach ($this->tuples as [$key, $value]) {
            yield $key => $value;
        }
    }

    /**
     * Return an array representing the values
     *
     * @return array<string|int, TValue>
     */
    public function toArray(): array
    {
        $values = [];
        foreach ($this->tuples as [$key,$value]) {
            $values[$this->convertToKey($key)] = $value;
        }

        return $values;
    }

    private function convertToKey(mixed $key): int|string
    {
        if (is_int($key) || is_string($key)) {
            return $key;
        }
        if ($key === null) {
            throw new GenericException('Invalid array key');
        }
        if (is_object($key)) {
            if (!($key instanceof Stringable)) {
                throw new GenericException('Invalid array key, object is not stringable');
            }

            return $key->__toString();
        }
        throw new GenericException('Invalid array key');
    }

    /**
     * Return a value in the map by index
     *
     * if index is not found, default value is returned
     *
     * @param TKey $searchedKey
     * @param ?TValue $defaultValue
     *
     * @return ?TValue
     */
    public function get(mixed $searchedKey, mixed $defaultValue = null): mixed
    {
        $convertedSearchToKey = $this->convertToKey($searchedKey);
        foreach ($this->tuples as [$key, $value]) {
            if ($this->convertToKey($key) === $convertedSearchToKey) {
                return $value;
            }
        }

        return $defaultValue;
    }

    /**
     * @param TKey $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        $convertedOffset = $this->convertToKey($offset);
        foreach ($this->tuples as [$key, $value]) {
            if ($this->convertToKey($key) === $convertedOffset) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TKey $offset
     *
     * @return TValue
     */
    public function offsetGet(mixed $offset): mixed
    {
        $convertedOffset = $this->convertToKey($offset);
        foreach ($this->tuples as [$key, $value]) {
            if ($this->convertToKey($key) === $convertedOffset) {
                return $value;
            }
        }
        throw new OutOfBoundsException(sprintf('Index %s does not exists', (string) $offset));
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            throw new InvalidArgumentException('Key must not be null');
        }
        $instance = $this->getInstance();
        $instance->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        if ($this->isImmutable()) {
            throw new LogicException('Can not unset value in a Immutable map');
        }
        $keyIndex = $this->keyIndex($offset);
        if ($keyIndex === -1) {
            throw new OutOfBoundsException(sprintf('Index %s does not exists', (string) $offset));
        }

        unset($this->tuples[$keyIndex]);
    }

    /**
     * Return an iterator of keys
     *
     * @return Generator<TKey>
     */
    public function keys(): Generator
    {
        foreach ($this->tuples as [$key, $value]) {
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
        foreach ($this->tuples as [$key, $value]) {
            yield $value;
        }
    }

    /**
     * Return the first value in the map
     *
     * EmptySetException is thrown if map is empty and $throw === true
     *
     * @return TValue|null
     */
    public function first(bool $throw = true): mixed
    {
        if (count($this->tuples) > 0) {
            reset($this->tuples);

            return current($this->tuples)[1];
        }
        if ($throw) {
            throw new EmptySetException('Empty set when calling first');
        }

        return null;
    }

    /**
     * Return the latest value in the map
     *
     * EmptySetException is thrown if map is empty and $throw === true
     *
     * @return TValue|null
     */
    public function last(bool $throw = true): mixed
    {
        if (count($this->tuples) > 0) {
            end($this->tuples);

            return current($this->tuples)[1];
        }
        if ($throw) {
            throw new EmptySetException('Empty set when calling last');
        }

        return null;
    }

    /**
     * Applies the callback to the values, keys are preserved
     *
     * Callback receive `$value` and `$index`
     *
     * @template TResult
     *
     * @param callable(TValue, TKey):TResult $callable
     *
     * @return static<TKey, TResult>
     *
     * @psalm-suppress  ImplementedReturnTypeMismatch
     */
    public function map(callable $callable)
    {
        $tuples = [];
        foreach ($this->tuples as [$key, $value]) {
            $tuples[] = [$key, $callable($value, $key)];
        }

        return new static($tuples);
    }

    /**
     * Iteratively reduce the Map to a single value using a callback function
     * Callback receive `$accumulator`,`$value` and `$key`
     *
     * @template TAccumulator
     *
     * @param callable(TAccumulator, TValue, TKey): TAccumulator $callable
     * @param TAccumulator $accumulator
     *
     * @return TAccumulator
     */
    public function reduce(callable $callable, mixed $accumulator): mixed
    {
        foreach ($this->tuples as [$key, $value]) {
            $accumulator = $callable($accumulator, $value, $key);
        }

        return $accumulator;
    }

    /**
     * Filter the map using a callback function
     *
     * Callback receive `$value`,`$key` and `$index`
     *
     * @param callable(TValue, TKey, int):bool $callable
     */
    public function filter(callable $callable): static
    {
        $instance = $this->getInstance();
        $instance->tuples = (new Filter())($instance->tuples, $callable);

        return $instance;
    }

    /**
     * Sort the map using a callback function
     * callback is of callable(TValue, TValue, TKey, TKey, int, int): int
     * and must return -1,0,1 as spaceship operator
     *
     * Callback receive `$valueA`,`$valueB`,`$keyA`,`$keyB`, `$indexA` and `$indexB`
     *
     * @param callable(TValue, TValue, TKey, TKey, int, int): int $callable
     */
    public function sort(callable $callable): static
    {
        $instance = $this->getInstance();
        $instance->tuples = (new Sort())($instance->tuples, $callable);

        return $instance;
    }

    /**
     * Return a map of all items where keys are not in argument map
     *
     * @param AbstractMap<TKey,TValue> $compared
     */
    public function keyDiff(AbstractMap $compared): static
    {
        return $this->apply(fn (AbstractMap $instance) => (new KeyDiff())($instance->tuples, $compared));
    }

    /**
     * Return a map of all items where keys are in arguments map
     *
     * @param AbstractMap<TKey,TValue> $compared
     */
    public function keyIntersect(AbstractMap $compared): static
    {
        return $this->apply(fn (AbstractMap $instance) => (new KeyIntersect())($instance->tuples, $compared));
    }

    /**
     * Return a map of all items where values are not in arguments map
     *
     * @param AbstractMap<TKey,TValue> $compared
     */
    public function valueDiff(AbstractMap $compared): static
    {
        return $this->apply(fn (AbstractMap $instance) => (new ValueDiff())($instance->tuples, $compared));
    }

    /**
     * Return a map of all items where values are in argument map
     *
     * @param AbstractMap<TKey,TValue> $compared
     */
    public function valueIntersect(AbstractMap $compared): static
    {
        return $this->apply(fn (AbstractMap $instance) => (new ValueIntersect())($instance->tuples, $compared));
    }

    /**
     * Extract a slice of the map
     *
     * @psalm-suppress  InvalidArgument
     */
    public function slice(int $offset, ?int $length = null): static
    {
        $max = $this->getSliceMax(count($this->tuples), $offset, $length);

        return $this->filter(fn (mixed $value, mixed $key, int $index) => $index >= $offset && $index <= $max);
    }

    /**
     * Apply a callback on set values
     *
     * Callback receive `$value`,`$key` and `$index`
     *
     * @param callable(TValue, TKey, int):bool $callable
     */
    public function forEach(callable $callable): static
    {
        foreach ($this->tuples as $index => [$key, $value]) {
            if (!$callable($value, $key, $index)) {
                return $this;
            }
        }

        return $this;
    }

    /**
     * Replace all values by applying a callback to the current instance
     *
     * @param callable(static): list<array{TKey,TValue}> $callable
     *
     * @phan-suppress PhanUnextractableAnnotationElementName, PhanUnextractableAnnotationSuffix
     */
    public function apply(callable $callable): static
    {
        $instance = $this->getInstance(true);
        $tuples = $callable($instance);
        $instance->tuples = $tuples;

        return $instance;
    }
}
