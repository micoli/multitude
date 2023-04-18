<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests\Map;

use Micoli\Multitude\Exception\EmptySetException;
use Micoli\Multitude\Exception\GenericException;
use Micoli\Multitude\Exception\InvalidArgumentException;
use Micoli\Multitude\Exception\OutOfBoundsException;
use Micoli\Multitude\Map\AbstractMap;
use Micoli\Multitude\Map\ImmutableMap;
use Micoli\Multitude\Map\MutableMap;
use Micoli\Multitude\Tests\Fixtures\Baz;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class MapTest extends TestCase
{
    /**
     * @return iterable<class-string, list<class-string<AbstractMap<mixed,mixed>>>>
     */
    public static function provideMapClass(): iterable
    {
        yield MutableMap::class => [MutableMap::class];
        yield ImmutableMap::class => [ImmutableMap::class];
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_instantiate_map(string $className): void
    {
        $map = new $className([['a', 1], ['b', 3], [3, 'a']]);
        self::assertSame([
            'a' => 1,
            'b' => 3,
            3 => 'a',
        ], $map->toArray());
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_not_convert_to_array_with_null_key(string $className): void
    {
        $map = new $className([['a', 1], [null, 3], [3, 'a']]);
        self::expectException(GenericException::class);
        $map->toArray();
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_not_convert_to_array_with_invalid_key(string $className): void
    {
        $map = new $className([['a', 1], [json_decode('{"aa":1}'), 3], [3, 'a']]);
        self::expectException(GenericException::class);
        $map->toArray();
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_remove_value_by_unknown_object_value(string $className): void
    {
        $baz = new Baz(1);
        $baz2 = new Baz(1);
        $map = $className::fromIterable(['a' => $baz, 'b' => 3, 3 => $baz]);
        $map->removeValue($baz2);
        self::assertCount(3, $map);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_remove_value_by_known_object_value(string $className): void
    {
        $baz = new Baz(1);
        $baz2 = new Baz(1);
        $map = $className::fromIterable(['a' => $baz, 'b' => 3, 3 => $baz, 'c' => 3, 'd' => 4, 'f' => 1]);
        $newMap = $map->removeValue($baz);
        self::assertCount(4, $newMap);
        $newMap2 = $newMap->removeValue(3);
        self::assertCount(2, $newMap2);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_be_counted(string $className): void
    {
        $map = new $className(['a' => 1, 'b' => 3, 3 => 2]);
        self::assertCount(3, $map);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_be_iterated(string $className): void
    {
        /** @var AbstractMap<int|string, int> $map */
        $map = new $className([['a', 1], ['b', 3], [3, 2]]);
        $result = [];
        foreach ($map as $k => $v) {
            $result[] = [$k, $v];
        }
        self::assertSame([['a', 1], ['b', 3], [3, 2]], $result);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_be_accessed_as_an_array(string $className): void
    {
        $map = new $className([['a', 1], ['b', 3], [3, '3 as int'], ['3', '3 as string']]);
        self::assertSame('3 as int', $map[3]);
        self::assertSame('3 as string', $map['3']);
        if ($className === MutableMap::class) {
            $map['toto'] = 123;
            self::assertSame(123, $map['toto']);
            self::assertArrayHasKey('toto', $map);
            self::assertArrayNotHasKey('tata', $map);
        }
        self::expectException(OutOfBoundsException::class);
        self::assertSame('3 as string', $map['unknown']);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_be_tested_as_empty(string $className): void
    {
        self::assertTrue((new $className([]))->isEmpty());
        self::assertFalse((new $className([['a', 1], ['b', 3], [3, '3 as int'], ['3', '3 as string']]))->isEmpty());
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_be_ammended_as_an_array(string $className): void
    {
        $map = new $className(['a' => 1, 'b' => 3, 3 => '3 as int']);
        self::expectException(InvalidArgumentException::class);
        /** @psalm-suppress NullArrayOffset */
        $map[null] = 'impossible';
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_iterate_keys(string $className): void
    {
        /** @var AbstractMap<int|string,int|string> $map */
        $map = new $className([['a', 1], ['b', 3], [3, '3 as int'], ['3', '3 as string']]);
        $keyList = [];
        foreach ($map->keys() as $k) {
            $keyList[] = $k;
        }
        self::assertSame(['a', 'b', 3, '3'], $keyList);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_update_by_key(string $className): void
    {
        /** @var AbstractMap<int|string,int|string> $map */
        $map = new $className([['a', 1], ['b', 3], [3, '3 as int'], ['3', '3 as string']]);
        $newMap = $map->set('a', 111);
        $keyList = [];
        foreach ($newMap->values() as $k) {
            $keyList[] = $k;
        }
        self::assertSame([111, 3, '3 as int', '3 as string'], $keyList);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_iterate_values(string $className): void
    {
        /** @var AbstractMap<int|string,int|string> $map */
        $map = new $className([['a', 1], ['b', 3], [3, '3 as int'], ['3', '3 as string']]);
        $valueList = [];
        foreach ($map->values() as $v) {
            $valueList[] = $v;
        }
        self::assertSame([1, 3, '3 as int', '3 as string'], $valueList);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_get_first_element(string $className): void
    {
        /** @var AbstractMap<mixed, mixed> $map */
        $map = $className::fromIterable(['a', 'b', 3, 0, null]);
        self::assertSame('a', $map->first());

        /** @var AbstractMap<mixed, mixed> $map */
        $map = new $className([]);
        self::assertSame(null, $map->first(false));

        /** @var AbstractMap<mixed, mixed> $map */
        $map = new $className([]);
        self::expectException(EmptySetException::class);
        $map->first();
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_get_last_element(string $className): void
    {
        /** @var AbstractMap<mixed,mixed> $map */
        $map = $className::fromIterable(['a' => 1, 'b' => 3, 3 => '3 as int']);
        self::assertSame('3 as int', $map->last());

        /** @var AbstractMap<mixed,mixed> $map */
        $map = new $className([]);
        self::assertSame(null, $map->last(false));

        /** @var AbstractMap<mixed,mixed> $map */
        $map = new $className([]);
        self::expectException(EmptySetException::class);
        $map->last();
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_map_values(string $className): void
    {
        /** @var AbstractMap<string|int,string|int> $map */
        $map = $className::fromIterable(['a' => 1, 'b' => 3, 3 => '3 as int']);
        $newMap = $map->map(fn (string|int $value, string|int $key): string => sprintf('%s=>%s', (string) $key, (string) $value));
        self::assertInstanceOf($className, $newMap);
        self::assertSame([
            'a' => 'a=>1',
            'b' => 'b=>3',
            3 => '3=>3 as int',
        ], $newMap->toArray());
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_reduce_values(string $className): void
    {
        /** @var AbstractMap<mixed,mixed> $map */
        $map = $className::fromIterable(['a' => 1, 'b' => 3, 3 => '3 as int']);
        $result = $map->reduce(function (array $accumulator, mixed $value, mixed $key): array {
            $accumulator[] = [$value, $key];

            return $accumulator;
        }, [[-1, 'initial']]);
        self::assertSame([
            [-1, 'initial'],
            [1, 'a'],
            [3, 'b'],
            ['3 as int', 3],
        ], $result);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_reduce_to_a_single_value(string $className): void
    {
        /** @var AbstractMap<int, int> $map */
        $map = $className::fromIterable([1 => 1, 2 => 2, 3 => 3]);
        $result = $map->reduce(function (int $accumulator, mixed $value, mixed $key): int {
            $accumulator += $value * $key;

            return $accumulator;
        }, 0);
        self::assertSame(14, $result);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_use_foreach(string $className): void
    {
        /** @var AbstractMap<int,int> $map */
        $map = $className::fromIterable([1 => 1, 2 => 2, 3 => 3, 4 => 4]);

        $result = [];
        $newMap = $map->foreach(function (int $value, int $key, int $index) use (&$result): bool {
            /** @var list<array{int,int}> $result */
            $result[] = [$key, $value];

            return $value !== 3;
        })->foreach(fn (int $value, int $key, int $index): bool => true);
        self::assertInstanceOf($className, $newMap);
        self::assertSame([[1, 1], [2, 2], [3, 3]], $result);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_be_sorted_by_value(string $className): void
    {
        /** @var AbstractMap<string, int> $map */
        $map = $className::fromIterable(['a' => 1, 'b' => 2, 'c' => 3]);
        $result = $map->sort(fn (int $valueA, int $valueB, string $keyA, string $keyB, int $indexA, int $indexB) => $valueB <=> $valueA);
        if ($map instanceof MutableMap) {
            /** @psalm-suppress TypeDoesNotContainType */
            self::assertSame($result, $map);
        } else {
            self::assertNotSame($result, $map);
        }
        self::assertSame(
            ['c' => 3, 'b' => 2, 'a' => 1],
            $result->toArray(),
        );
        self::assertSame([0, 1, 2], array_keys($result->getTuples()));
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_be_sorted_by_key(string $className): void
    {
        /** @var AbstractMap<string, int> $map */
        $map = $className::fromIterable(['a' => 1, 'b' => 2, 'c' => 3]);
        $result = $map->sort(fn (int $valueA, int $valueB, string $keyA, string $keyB, int $indexA, int $indexB) => $keyB <=> $keyA);
        if ($map instanceof MutableMap) {
            /** @psalm-suppress TypeDoesNotContainType */
            self::assertSame($result, $map);
        } else {
            self::assertNotSame($result, $map);
        }
        self::assertSame(['c' => 3, 'b' => 2, 'a' => 1], $result->toArray());
        self::assertSame([0, 1, 2], array_keys($result->getTuples()));
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap<mixed, mixed>> $className
     */
    public function it_should_be_sorted_by_index(string $className): void
    {
        /** @var AbstractMap<string, int> $map */
        $map = $className::fromIterable(['a' => 1, 'b' => 2, 'c' => 3]);
        $result = $map->sort(fn (int $valueA, int $valueB, string $keyA, string $keyB, int $indexA, int $indexB) => $indexB <=> $indexA);
        if ($map instanceof MutableMap) {
            /** @psalm-suppress TypeDoesNotContainType */
            self::assertSame($result, $map);
        } else {
            self::assertNotSame($result, $map);
        }
        self::assertSame(['c' => 3, 'b' => 2, 'a' => 1], $result->toArray());
        self::assertSame([0, 1, 2], array_keys($result->getTuples()));
    }
}
