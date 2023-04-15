<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests\Map;

use Micoli\Multitude\Exception\EmptySetException;
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
     * @param class-string<AbstractMap> $className
     */
    public function it_should_instantiate_map(string $className): void
    {
        /** @var MutableMap<mixed,mixed> $map */
        $map = $className::fromArray(['a' => 1, 'b' => 3, 3 => 'a']);
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
     * @param class-string<AbstractMap> $className
     */
    public function it_should_remove_value_by_unknown_object_value(string $className): void
    {
        $baz = new Baz(1);
        $baz2 = new Baz(1);
        /** @var MutableMap<mixed,mixed> $map */
        $map = $className::fromArray(['a' => $baz, 'b' => 3, 3 => $baz]);
        $map->removeValue($baz2);
        self::assertCount(3, $map);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap> $className
     */
    public function it_should_be_counted(string $className): void
    {
        /** @var MutableMap<mixed,mixed> $map */
        $map = $className::fromArray(['a' => 1, 'b' => 3, 3 => 2]);
        self::assertCount(3, $map);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap> $className
     */
    public function it_should_be_iterated(string $className): void
    {
        /** @var MutableMap<mixed,mixed> $map */
        $map = $className::fromArray(['a' => 1, 'b' => 3, 3 => 2]);
        $result = '';
        foreach ($map as $k => $v) {
            $result = sprintf('%s,%s=>%s', $result, $k, $v);
        }
        self::assertSame(',a=>1,b=>3,3=>2', $result);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap> $className
     */
    public function it_should_be_accessed_as_an_array(string $className): void
    {
        /** @var MutableMap<mixed,mixed> $map */
        $map = $className::fromTuples([['a', 1], ['b', 3], [3, '3 as int'], ['3', '3 as string']]);
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
     * @param class-string<AbstractMap> $className
     */
    public function it_should_be_tested_as_empty(string $className): void
    {
        self::assertTrue($className::fromTuples([])->isEmpty());
        self::assertFalse($className::fromTuples([['a', 1], ['b', 3], [3, '3 as int'], ['3', '3 as string']])->isEmpty());
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap> $className
     */
    public function it_should_be_ammended_as_an_array(string $className): void
    {
        /** @var MutableMap<mixed,mixed> $map */
        $map = $className::fromArray(['a' => 1, 'b' => 3, 3 => '3 as int']);
        self::expectException(InvalidArgumentException::class);
        /** @psalm-suppress NullArrayOffset */
        $map[null] = 'impossible';
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap> $className
     */
    public function it_should_iterate_keys(string $className): void
    {
        /** @var MutableMap<mixed,mixed> $map */
        $map = $className::fromTuples([['a', 1], ['b', 3], [3, '3 as int'], ['3', '3 as string']]);
        $keyList = '';
        foreach ($map->keys() as $k) {
            $keyList .= $k . ',';
        }
        self::assertSame('a,b,3,3,', $keyList);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap> $className
     */
    public function it_should_update_by_key(string $className): void
    {
        /** @var MutableMap<mixed,mixed> $map */
        $map = $className::fromTuples([['a', 1], ['b', 3], [3, '3 as int'], ['3', '3 as string']]);
        $newMap = $map->set('a', 111);
        $keyList = '';
        foreach ($newMap->values() as $k) {
            $keyList .= $k . ',';
        }
        self::assertSame('111,3,3 as int,3 as string,', $keyList);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap> $className
     */
    public function it_should_iterate_values(string $className): void
    {
        /** @var MutableMap<mixed,mixed> $map */
        $map = $className::fromTuples([['a', 1], ['b', 3], [3, '3 as int'], ['3', '3 as string']]);
        $valueList = '';
        foreach ($map->values() as $v) {
            $valueList .= $v . ',';
        }
        self::assertSame('1,3,3 as int,3 as string,', $valueList);
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap> $className
     */
    public function it_should_get_first_element(string $className): void
    {
        /** @var AbstractMap<mixed, mixed> $set */
        $set = $className::fromArray(['a', 'b', 3, 0, null]);
        self::assertSame('a', $set->first());

        /** @var AbstractMap<mixed, mixed> $set */
        $set = $className::fromArray([]);
        self::assertSame(null, $set->first(false));

        /** @var AbstractMap<mixed, mixed> $set */
        $set = $className::fromArray([]);
        self::expectException(EmptySetException::class);
        $set->first();
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap> $className
     */
    public function it_should_get_last_element(string $className): void
    {
        /** @var AbstractMap<mixed,mixed> $map */
        $map = $className::fromArray(['a' => 1, 'b' => 3, 3 => '3 as int']);
        self::assertSame('3 as int', $map->last());

        /** @var AbstractMap<mixed,mixed> $map */
        $map = $className::fromArray([]);
        self::assertSame(null, $map->last(false));

        /** @var AbstractMap<mixed,mixed> $map */
        $map = $className::fromArray([]);
        self::expectException(EmptySetException::class);
        $map->last();
    }

    /**
     * @test
     *
     * @dataProvider provideMapClass
     *
     * @param class-string<AbstractMap> $className
     */
    public function it_should_map_values(string $className): void
    {
        /** @var AbstractMap<mixed,mixed> $map */
        $map = $className::fromArray(['a' => 1, 'b' => 3, 3 => '3 as int']);
        $newMap = $map->map(fn (mixed $value, mixed $key): string => sprintf('%s=>%s', $key, $value));
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
     * @param class-string<AbstractMap> $className
     */
    public function it_should_reduce_values(string $className): void
    {
        /** @var AbstractMap<mixed,mixed> $map */
        $map = $className::fromArray(['a' => 1, 'b' => 3, 3 => '3 as int']);
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
     * @param class-string<AbstractMap> $className
     */
    public function it_should_reduce_to_a_single_value(string $className): void
    {
        /** @var AbstractMap<mixed,mixed> $map */
        $map = $className::fromArray([1 => 1, 2 => 2, 3 => 3]);
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
     * @param class-string<AbstractMap> $className
     */
    public function it_should_use_foreach(string $className): void
    {
        /** @var AbstractMap<mixed,mixed> $map */
        $map = $className::fromArray([1 => 1, 2 => 2, 3 => 3]);
        $result = '';
        $newMap = $map->foreach(function (mixed $value, mixed $key, int $index) use (&$result): bool {
            $result = sprintf('%s,%s=>%s', $result, $value, $key);

            return $value !== 3;
        });
        self::assertInstanceOf($className, $newMap);
        self::assertSame(',1=>1,2=>2,3=>3', $result);
    }
}
