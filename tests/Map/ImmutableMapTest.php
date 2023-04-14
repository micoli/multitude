<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests\Map;

use InvalidArgumentException;
use LogicException;
use Micoli\Multitude\Map\ImmutableMap;
use Micoli\Multitude\Map\MutableMap;
use Micoli\Multitude\Tests\Fixtures\Baz;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ImmutableMapTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_instantiate_and_modify_a_map(): void
    {
        /** @var ImmutableMap<mixed, mixed> $map */
        $map = ImmutableMap::fromTuples([['a', 1], ['b', 3], [3, 'a']]);
        $newMap = $map
            ->set('2', '2 as string')
            ->set(2, '2 as int');
        self::assertCount(3, $map);
        self::assertCount(5, $newMap);
        self::assertSame('2 as string', $newMap->get('2'));
        self::assertSame('2 as int', $newMap->get(2));
        self::expectException(InvalidArgumentException::class);
        $map->set(null, 'can not be added');
    }

    /**
     * @test
     */
    public function it_should_remove_value_by_key(): void
    {
        /** @var ImmutableMap<mixed, mixed> $map */
        $map = ImmutableMap::fromTuples([['a', 1], ['b', 3], [3, 'a'], ['2', '2 as string'], [2, '2 as int']]);
        $newMap = $map->removeKey(2);
        self::assertSame('2 as string', $map->get('2'));
        self::assertSame('2 as int', $map->get(2));
        self::assertCount(5, $map);
        self::assertCount(4, $newMap);
        self::assertNull($newMap->get(2));
    }

    /**
     * @test
     */
    public function it_should_remove_value_by_value(): void
    {
        /** @var ImmutableMap<mixed, mixed> $map */
        $map = ImmutableMap::fromTuples([['a', 1], ['b', 3], [3, 'a'], ['2', '2 as string'], [2, '2 as int'], ['22', '2 as string']]);
        $newMap = $map->removeValue('2 as string');
        self::assertNotNull($map->get('2'));
        self::assertNotNull($map->get('22'));
        self::assertSame('2 as int', $map->get(2));
        self::assertNull($newMap->get('2'));
        self::assertNull($newMap->get('22'));
        self::assertSame('2 as int', $newMap->get(2));
    }

    /**
     * @test
     */
    public function it_should_remove_value_by_object_value(): void
    {
        $baz = new Baz(1);
        /** @var ImmutableMap<mixed, mixed> $map */
        $map = ImmutableMap::fromArray(['a' => $baz, 'b' => 3, 3 => $baz]);
        $newMap = $map->removeValue($baz);
        self::assertCount(3, $map);
        self::assertCount(1, $newMap);
    }

    /**
     * @test
     */
    public function it_should_be_unset_as_an_array(): void
    {
        /** @var ImmutableMap<mixed, mixed> $map */
        $map = ImmutableMap::fromTuples([['a', 1], ['b', 3], [3, '3 as int'], ['3', '3 as string']]);
        self::assertTrue($map->hasKey(3));
        self::assertTrue($map->hasKey('3'));
        self::expectException(LogicException::class);
        unset($map[3]);
    }

    /**
     * @test
     */
    public function it_should_be_converted_as_mutable(): void
    {
        /** @var ImmutableMap<mixed, mixed> $map */
        $map = ImmutableMap::fromArray(['a' => 1, 'b' => 3, 3 => '3 as int']);
        $newMap = $map->toMutable();
        self::assertInstanceOf(MutableMap::class, $newMap);
        self::assertSame($map->toArray(), $newMap->toArray());
    }

    /**
     * @test
     */
    public function it_should_filter_map(): void
    {
        /** @var ImmutableMap<mixed,mixed> $map */
        $map = ImmutableMap::fromTuples([[1, 1], [2, 2], [3, 3], ['3', '3']]);
        $newMap = $map->filter(fn (mixed $value, mixed $key): bool => $key === 1 || $value === '3');
        self::assertInstanceOf(ImmutableMap::class, $newMap);
        self::assertSame([[1, 1], [2, 2], [3, 3], ['3', '3']], $map->getTuples());
        self::assertSame([[1, 1], ['3', '3']], $newMap->getTuples());
    }

    /**
     * @test
     */
    public function it_should_slice_map(): void
    {
        /** @var ImmutableMap<mixed,mixed> $map */
        $map = ImmutableMap::fromTuples([[1, 1], [2, 2], [3, 3], ['3', '3'], ['4', '4']]);
        $newMap = $map->slice(1, 2);
        self::assertInstanceOf(ImmutableMap::class, $newMap);
        self::assertSame([[1, 1], [2, 2], [3, 3], ['3', '3'], ['4', '4']], $map->getTuples());
        self::assertSame([[2, 2], [3, 3], ['3', '3']], $newMap->getTuples());

        $newMap2 = $map->slice(1);
        self::assertInstanceOf(ImmutableMap::class, $newMap2);
        self::assertSame([[2, 2], [3, 3], ['3', '3'], ['4', '4']], $newMap2->getTuples());

        self::expectException(InvalidArgumentException::class);
        $map->slice(1, -1);
    }
}
