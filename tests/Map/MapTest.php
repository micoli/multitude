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
}
