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
use Micoli\Multitude\Set\Operation\Filter;
use Micoli\Multitude\Set\Operation\KeyDiff;
use Micoli\Multitude\Set\Operation\KeyIntersect;
use Micoli\Multitude\Set\Operation\Sort;
use Micoli\Multitude\Set\Operation\ValueDiff;
use Micoli\Multitude\Set\Operation\ValueIntersect;
use Traversable;

/**
 * @template TValue
 *
 * @implements \IteratorAggregate<TValue>
 *
 * @phpstan-consistent-constructor
 *
 * @psalm-consistent-constructor
 *
 * @psalm-consistent-templates
 */
class AbstractSet extends AbstractMultitude implements IteratorAggregate, Countable
{
    /**
     * @var list<TValue>
     */
    protected array $values;
    private bool $isMutable;

    /**
     * @param iterable<TValue> $values
     *
     * @codeCoverageIgnore
     */
    public function __construct(iterable $values = [])
    {
        if (!($this instanceof MutableInterface) && !($this instanceof ImmutableInterface)) {
            throw new LogicException('Set must be either Mutable or Immutable');
        }
        if (($this instanceof MutableInterface) == ($this instanceof ImmutableInterface)) {
            throw new LogicException('Set must be either Mutable or Immutable');
        }
        $this->isMutable = $this instanceof MutableInterface;
        $this->values = [];
        foreach ($values as $value) {
            if (in_array($value, $this->values, true)) {
                continue;
            }
            $this->values[] = $value;
        }
    }

    /**
     * return the number of items in the set
     */
    public function count(): int
    {
        return count($this->values);
    }

    /**
     * @return static
     */
    private function getInstance(bool $forceNew = false)
    {
        if ($forceNew) {
            return clone $this;
        }

        return $this->isMutable ? $this : clone ($this);
    }

    /**
     * Append a value at the end of the set
     *
     * Throw a ValueAlreadyPresentException if value is already present in the set and $throw==true
     *
     * @param TValue $newValue
     */
    public function append(mixed $newValue, bool $throw = true): static
    {
        $instance = $this->getInstance();
        /**
         * @var TValue $value
         */
        foreach ($instance->values as $value) {
            if ($value === $newValue) {
                if ($throw) {
                    throw new ValueAlreadyPresentException(sprintf('Value %s already present', (string) $newValue));
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
        throw new NotFoundException(sprintf('Value %s not found', (string) $searchedValue));
    }

    /**
     * @param TValue $searchedValue
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
     * Return if a set contains an index
     */
    public function hasIndex(int $index): bool
    {
        return array_key_exists($index, $this->values);
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
     * @param ?TValue $defaultValue
     *
     * @return ?TValue
     */
    public function get(int $index, mixed $defaultValue = null): mixed
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
     * @return TValue|null
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
     * @return TValue|null
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
     * @param callable(TValue, int): TResult $callable
     *
     * @return static<TResult>
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function map(callable $callable)
    {
        $values = [];
        foreach ($this->values as $index => $value) {
            $values[] = $callable($value, $index);
        }

        return new static($values);
    }

    /**
     * Iteratively reduce the Set to a single value using a callback function
     * Callback receive `$accumulator`,`$value` and `$index`
     *
     * @template TAccumulator
     *
     * @param callable(TAccumulator, TValue, int):TAccumulator $callable
     * @param TAccumulator $accumulator
     *
     * @return TAccumulator
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
     */
    public function filter(callable $callable): static
    {
        $instance = $this->getInstance();

        $instance->values = (new Filter())($instance->values, $callable);

        return $instance;
    }

    /**
     * Sort the map using a callback function
     * callback is of callable (TValue, TValue, int, int): int
     * and must return -1,0,1 as spaceship operator
     *
     * Callback receive `$valueA`,`$valueB`, `$indexA` and `$indexB`
     *
     * @param callable(TValue, TValue, int, int): int $callable
     */
    public function sort(callable $callable): static
    {
        $instance = $this->getInstance();
        $instance->values = (new Sort())($instance->values, $callable);

        return $instance;
    }

    /**
     * Return a set of all items where keys are not in argument set
     *
     * @param AbstractSet<TValue> $compared
     */
    public function indexDiff(AbstractSet $compared): static
    {
        return $this->apply(fn (AbstractSet $instance) => (new KeyDiff())($instance->values, $compared));
    }

    /**
     * Return a map of all items where keys are in arguments map
     *
     * @param AbstractSet<TValue> $compared
     */
    public function indexIntersect(AbstractSet $compared): static
    {
        return $this->apply(fn (AbstractSet $instance) => (new KeyIntersect())($instance->values, $compared));
    }

    /**
     * Return a Set of all items where values are not in argument set
     *
     * @param AbstractSet<TValue> $compared
     */
    public function valueDiff(AbstractSet $compared): static
    {
        return $this->apply(fn (AbstractSet $instance) => (new ValueDiff())($instance->values, $compared));
    }

    /**
     * Return a set of all items where values are in argument set
     *
     * @param AbstractSet<TValue> $compared
     */
    public function valueIntersect(AbstractSet $compared): static
    {
        return $this->apply(fn (AbstractSet $instance) => (new ValueIntersect())($instance->values, $compared));
    }

    /**
     * Extract a slice of the set
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

    /**
     * Replace all values by applying a callback to the current instance
     *
     * @param callable(static): list<TValue> $callable
     *
     * @phan-suppress PhanUnextractableAnnotationElementName, PhanUnextractableAnnotationSuffix
     */
    public function apply(callable $callable): static
    {
        $instance = $this->getInstance(true);
        $values = $callable($instance);
        $instance->values = $values;

        return $instance;
    }
}
