<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests\Set;

use Micoli\Multitude\Exception\EmptySetException;
use Micoli\Multitude\Set\AbstractSet;
use Micoli\Multitude\Set\ImmutableSet;
use Micoli\Multitude\Set\MutableSet;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class SetTest extends TestCase
{
    public static function provideMapClass(): iterable
    {
        yield MutableSet::class => [MutableSet::class];
        yield ImmutableSet::class => [ImmutableSet::class];
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractSet> $className
     */
    public function it_should_instantiate_a_set(string $className): void
    {
        /** @var ImmutableSet<mixed> $set */
        $set = $className::fromArray([1, 3, 4 => 'a']);
        self::assertSame([1, 3, 'a'], $set->toArray());
        self::assertSame([0, 1, 2], iterator_to_array($set->keys()));
        self::assertCount(3, $set);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractSet> $className
     */
    public function it_should_be_counted(string $className): void
    {
        /** @var ImmutableSet<mixed> $set */
        $set = $className::fromArray(['a', 'b', 3, 0, null]);
        self::assertCount(5, $set);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractSet> $className
     */
    public function it_should_be_iterated(string $className): void
    {
        /** @var ImmutableSet<mixed> $set */
        $set = $className::fromArray(['a', 'b', 3, 0, null]);
        $result = '';
        foreach ($set as $v) {
            $result = sprintf('%s,%s', $result, $v);
        }
        self::assertSame(',a,b,3,0,', $result);

        $result = '';
        foreach ($set->values() as $v) {
            $result = sprintf('%s,%s', $result, $v);
        }
        self::assertSame(',a,b,3,0,', $result);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractSet> $className
     */
    public function it_should_iterate_keys(string $className): void
    {
        /** @var ImmutableSet<mixed> $set */
        $set = $className::fromArray(['a', 'b', 3, 0, null]);
        $keyList = '';
        foreach ($set->keys() as $k) {
            $keyList .= $k . ',';
        }
        self::assertSame('0,1,2,3,4,', $keyList);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractSet> $className
     */
    public function it_should_get_first_element(string $className): void
    {
        /** @var ImmutableSet<mixed> $set */
        $set = $className::fromArray(['a', 'b', 3, 0, null]);
        self::assertSame('a', $set->first());

        /** @var ImmutableSet<mixed> $set */
        $set = $className::fromArray([]);
        self::assertSame(null, $set->first(false));

        /** @var ImmutableSet<mixed> $set */
        $set = $className::fromArray([]);
        self::expectException(EmptySetException::class);
        $set->first();
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractSet> $className
     */
    public function it_should_get_last_element(string $className): void
    {
        /** @var ImmutableSet<mixed> $set */
        $set = $className::fromArray(['a', 'b', 3, 0, null, 'last']);
        self::assertSame('last', $set->last());

        /** @var ImmutableSet<mixed> $set */
        $set = $className::fromArray([]);
        self::assertSame(null, $set->last(false));

        /** @var ImmutableSet<mixed> $set */
        $set = $className::fromArray([]);
        self::expectException(EmptySetException::class);
        $set->last();
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractSet> $className
     */
    public function it_should_map_values(string $className): void
    {
        /** @var AbstractSet<mixed> $set */
        $set = $className::fromArray(['a', 'b', 3, 0, null, 'last']);
        $newSet = $set->map(fn (mixed $value, mixed $key): string => sprintf('%s=>%s', $key, json_encode($value)));
        self::assertInstanceOf($className, $newSet);
        self::assertSame([
            '0=>"a"',
            '1=>"b"',
            '2=>3',
            '3=>0',
            '4=>null',
            '5=>"last"',
        ], $newSet->toArray());
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractSet> $className
     */
    public function it_should_reduce_values(string $className): void
    {
        /** @var AbstractSet<mixed> $map */
        $set = $className::fromArray(['a', 'b', 3, 0, null, 'last']);
        $result = $set->reduce(function (array $accumulator, mixed $value, mixed $index): array {
            $accumulator[] = [$index, $value];

            return $accumulator;
        }, [[-1, 'initial']]);
        self::assertSame([
            [-1, 'initial'],
            [0, 'a'],
            [1, 'b'],
            [2, 3],
            [3, 0],
            [4, null],
            [5, 'last'],
        ], $result);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractSet> $className
     */
    public function it_should_reduce_to_a_single_value(string $className): void
    {
        /** @var AbstractSet<mixed> $set */
        $set = $className::fromArray([1 => 1, 2 => 2, 3 => 3]);
        $result = $set->reduce(function (int $accumulator, mixed $value, mixed $index): int {
            $accumulator += $value * ($index + 1);

            return $accumulator;
        }, 0);
        self::assertSame(14, $result);
    }
}
