<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests\Map;

use Micoli\Multitude\Exception\InvalidArgumentException;
use Micoli\Multitude\Exception\OutOfBoundsException;
use Micoli\Multitude\Map\ImmutableMap;
use Micoli\Multitude\Map\MutableMap;
use Micoli\Multitude\Tests\Fixtures\Baz;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class MutableMapTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_instantiate_and_modify_a_map(): void
    {
        /** @var MutableMap<mixed,mixed> $map */
        $map = MutableMap::fromIterable(['a' => 1, 'b' => 3, 3 => 'a']);
        $map->set('2', '2 as string');
        $map->set(2, '2 as int');
        self::assertSame('2 as string', $map->get('2'));
        self::assertSame('2 as int', $map->get(2));
        self::expectException(InvalidArgumentException::class);
        $map->set(null, 'can not be added');
    }

    /**
     * @test
     */
    public function it_should_remove_value_by_key(): void
    {
        /** @var MutableMap<mixed,mixed> $map */
        $map = MutableMap::fromIterable(['a' => 1, 'b' => 3, 3 => 'a']);
        $map->set('2', '2 as string');
        $map->set(2, '2 as int');
        $map->removeKey(2);
        $map->removeKey('b');
        self::assertSame('2 as string', $map->get('2'));
        self::assertNull($map->get(2));
        self::assertSame([0, 1, 2], array_keys($map->getTuples()));
    }

    /**
     * @test
     */
    public function it_should_remove_value_by_value(): void
    {
        /** @var MutableMap<mixed,mixed> $map */
        $map = new MutableMap([['a', 1], ['b', 3], [3, 'a']]);
        $map->set('2', '2 as string');
        $map->set(2, '2 as int');
        $map->set('22', '2 as string');
        $map->removeValue('2 as string');
        self::assertNull($map->get('2'));
        self::assertNull($map->get('22'));
        self::assertSame('2 as int', $map->get(2));
    }

    /**
     * @test
     */
    public function it_should_remove_value_by_object_value(): void
    {
        $baz = new Baz(1);
        /** @var MutableMap<mixed,mixed> $map */
        $map = new MutableMap([['a', $baz], ['b', 3], [3, $baz]]);
        $map->removeValue($baz);
        self::assertCount(1, $map);
    }

    /**
     * @test
     */
    public function it_should_be_unset_as_an_array(): void
    {
        /** @var MutableMap<mixed,mixed> $map */
        $map = new MutableMap([['a', 1], ['b', 3], [3, '3 as int']]);
        $map->set('3', '3 as string');
        self::assertTrue($map->hasKey(3));
        self::assertTrue($map->hasKey('3'));
        unset($map[3]);
        self::assertFalse($map->hasKey(3));
        self::assertTrue($map->hasKey('3'));

        self::expectException(OutOfBoundsException::class);
        unset($map[99]);
    }

    /**
     * @test
     */
    public function it_should_be_converted_as_immutable(): void
    {
        /** @var MutableMap<mixed,mixed> $map */
        $map = new MutableMap([['a', 1], ['b', 3], [3, '3 as int']]);
        $newMap = $map->toImmutable();
        self::assertInstanceOf(ImmutableMap::class, $newMap);
        self::assertSame($map->toArray(), $newMap->toArray());
    }

    /**
     * @test
     */
    public function it_should_filter_map(): void
    {
        /** @var MutableMap<mixed,mixed> $map */
        $map = new MutableMap([[1, 1], [2, 2], [3, 3], ['3', '3']]);
        $map->filter(fn (mixed $value, mixed $key): bool => $key === 1 || $value === '3');
        self::assertInstanceOf(MutableMap::class, $map);
        self::assertSame([[1, 1], ['3', '3']], $map->getTuples());
    }

    /**
     * @test
     */
    public function it_should_slice_map(): void
    {
        /** @var MutableMap<mixed,mixed> $map */
        $map = new MutableMap([[1, 1], [2, 2], [3, 3], ['3', '3'], ['4', '4']]);
        $map->slice(1, 2);
        self::assertSame([[2, 2], [3, 3], ['3', '3']], $map->getTuples());

        /** @var MutableMap<mixed,mixed> $map */
        $map = new MutableMap([[1, 1], [2, 2], [3, 3], ['3', '3'], ['4', '4']]);
        $map->slice(1);
        self::assertSame([[2, 2], [3, 3], ['3', '3'], ['4', '4']], $map->getTuples());

        self::expectException(InvalidArgumentException::class);
        $map->slice(1, -1);
    }
}
