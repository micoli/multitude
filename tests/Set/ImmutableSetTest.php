<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests\Set;

use LogicException;
use Micoli\Multitude\Set\ImmutableSet;
use Micoli\Multitude\Set\MutableSet;
use Micoli\Multitude\Tests\Fixtures\Baz;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ImmutableSetTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_instantiate_and_modify_a_map(): void
    {
        /** @var ImmutableSet<mixed> $set */
        $set = ImmutableSet::fromArray([1, 3, 4 => 'a']);
        $newSet = $set->append('2');
        self::assertFalse($set->hasValue('2'));
        self::assertTrue($newSet->hasValue('2'));
        self::expectException(LogicException::class);
        $newSet->append(3);
    }

    /**
     * @test
     */
    public function it_should_remove_value_by_value(): void
    {
        /** @var ImmutableSet<mixed> $set */
        $set = ImmutableSet::fromArray([1, 3, 4 => 'a']);
        $newSet = $set->remove('a');
        self::assertTrue($set->hasValue('a'));
        self::assertFalse($newSet->hasValue('a'));
        self::expectException(LogicException::class);
        $newSet->remove('a');
    }

    /**
     * @test
     */
    public function it_should_remove_value_by_unknown_object_value(): void
    {
        $baz = new Baz(1);
        $baz2 = new Baz(1);
        /** @var ImmutableSet<mixed> $set */
        $set = ImmutableSet::fromArray([$baz, 3, $baz]);
        self::assertCount(2, $set);

        $set->remove($baz2, false);
        self::assertCount(2, $set);

        $newSet = $set->remove($baz, false);
        self::assertCount(2, $set);
        self::assertCount(1, $newSet);

        self::expectException(LogicException::class);
        $set->remove($baz2);
        self::assertCount(1, $set);
    }

    /**
     * @test
     */
    public function it_should_be_converted_as_immutable(): void
    {
        /** @var ImmutableSet<mixed> $set */
        $set = ImmutableSet::fromArray(['a', 'b', 3, 0, null]);
        $newMap = $set->toMutable();
        self::assertInstanceOf(MutableSet::class, $newMap);
        self::assertSame($set->toArray(), $newMap->toArray());
    }

    /**
     * @test
     */
    public function it_should_filter_map(): void
    {
        /** @var ImmutableSet<mixed> $map */
        $map = ImmutableSet::fromArray(['a', 'b', 3, 0, null]);
        $newMap = $map->filter(fn (mixed $value, mixed $index): bool => $index === 0 || $value === 'b');
        self::assertInstanceOf(ImmutableSet::class, $newMap);
        self::assertSame(['a', 'b', 3, 0, null], $map->toArray());
        self::assertSame(['a', 'b'], $newMap->toArray());
    }
}
