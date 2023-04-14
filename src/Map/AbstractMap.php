<?php

declare(strict_types=1);

namespace Micoli\Multitude\Map;

use ArrayAccess;
use Countable;
use Generator;
use IteratorAggregate;
use Micoli\Multitude\Exception\EmptySetException;
use Micoli\Multitude\Exception\InvalidArgumentException;
use Micoli\Multitude\Exception\LogicException;
use Micoli\Multitude\Exception\OutOfBoundsException;
use Micoli\Multitude\ImmutableInterface;
use Micoli\Multitude\MutableInterface;
use Traversable;

/**
 * @template TKey
 * @template TValue
 *
 * @implements IteratorAggregate<TKey, TValue>
 * @implements ArrayAccess<TKey, TValue>
 */
class AbstractMap implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @var list<array{TKey, TValue}>
     */
    protected array $tuples;
    private bool $isMutable;

    /**
     * @param list<array{TKey, TValue}> $tuples
     */
    protected function __construct(array $tuples = [])
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
     * @template TK
     * @template TV
     *
     * @param iterable<TK, TV> $values
     *
     * @return static<TK, TV>
     */
    public static function fromArray(iterable $values): static
    {
        $buffer = [];
        foreach ($values as $key => $value) {
            $buffer[] = [$key, $value];
        }
        /** @psalm-suppress UnsafeInstantiation */
        return new static($buffer);
    }

    /**
     * @template TK
     * @template TV
     *
     * @param list<array{TK, TV}> $values
     *
     * @return static<TK, TV>
     */
    public static function fromTuples(iterable $values): static
    {
        /** @psalm-suppress UnsafeInstantiation */
        return new static($values);
    }

    public function count(): int
    {
        return count($this->tuples);
    }

    private function isImmutable(): bool
    {
        return !$this->isMutable;
    }

    private function getInstance(): static
    {
        return $this->isMutable ? $this : clone ($this);
    }

    /**
     * @param TKey $newKey
     * @param TValue $newValue
     *
     * @return static<TKey, TValue>
     */
    public function set(mixed $newKey, mixed $newValue): static
    {
        if ($newKey === null) {
            throw new InvalidArgumentException('Key must not be null');
        }
        $instance = $this->getInstance();
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
     * @param TKey $searchedKey
     */
    public function removeKey(mixed $searchedKey): static
    {
        $instance = $this->getInstance();
        $keyIndex = $instance->keyIndex($searchedKey);
        if ($keyIndex !== -1) {
            unset($instance->tuples[$keyIndex]);
        }

        return $instance;
    }

    /**
     * @param TValue $searchedValue
     */
    public function removeValue(mixed $searchedValue): static
    {
        $instance = $this->getInstance();
        for ($index = count($instance->tuples); $index >= 0; --$index) {
            if ($instance->tuples[$index][1] === $searchedValue) {
                unset($instance->tuples[$index]);
            }
        }
        $instance->tuples = array_values($instance->tuples);

        return $instance;
    }

    /**
     * @param TKey $searchedKey
     *
     * @return int<-1, max>
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
     * @param TKey $searchedKey
     */
    public function hasKey(mixed $searchedKey): bool
    {
        return $this->keyIndex($searchedKey) >= 0;
    }

    public function isEmpty(): bool
    {
        return count($this->tuples) === 0;
    }

    public function getIterator(): Traversable
    {
        foreach ($this->tuples as [$key, $value]) {
            yield $key => $value;
        }
    }

    /**
     * @return array<TKey, TValue>
     */
    public function toArray(): array
    {
        return iterator_to_array($this->getIterator());
    }

    /**
     * @param TKey $searchedKey
     * @param ?TValue $defaultValue
     *
     * @return TValue
     */
    public function get(mixed $searchedKey, mixed $defaultValue = null): mixed
    {
        foreach ($this->tuples as [$key, $value]) {
            if ($key === $searchedKey) {
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
        foreach ($this->tuples as [$key, $value]) {
            if ($key === $offset) {
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
        foreach ($this->tuples as [$key, $value]) {
            if ($key === $offset) {
                return $value;
            }
        }
        throw new OutOfBoundsException(sprintf('Index %s does not exists', $offset));
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
            throw new OutOfBoundsException(sprintf('Index %s does not exists', $offset));
        }

        unset($this->tuples[$keyIndex]);
    }

    /**
     * @return Generator<TKey>
     */
    public function keys(): Generator
    {
        foreach ($this->tuples as [$key, $value]) {
            yield $key;
        }
    }

    /**
     * @return Generator<TValue>
     */
    public function values(): Generator
    {
        foreach ($this->tuples as [$key, $value]) {
            yield $value;
        }
    }

    /**
     * @return TValue
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
     * @return TValue
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
     * @template TResult
     *
     * @param callable(TValue, TKey):TResult $callable
     *
     * @return static<TKey, TResult>
     *
     * @psalm-suppress  InvalidArgument
     */
    public function map(callable $callable): static
    {
        /** @var static<TKey, TResult> $instance */
        $instance = $this->getInstance();
        foreach ($instance->tuples as $index => [$key, $value]) {
            $instance->tuples[$index] = [$key, $callable($value, $key)];
        }

        return $instance;
    }
}
