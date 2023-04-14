<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests\Set;

use LogicException;
use Micoli\Multitude\Exception\InvalidArgumentException;
use Micoli\Multitude\Set\MutableSet;
use Micoli\Multitude\Tests\Fixtures\Baz;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class MutableSetTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_instantiate_and_modify_a_map(): void
    {
        /** @var MutableSet<mixed> $set */
        $set = MutableSet::fromArray([1, 3, 4 => 'a']);
        $set->append('2');
        $set->append(2);
        $set->append('3');
        $set->append(5);
        self::assertTrue($set->hasValue('2'));
        self::assertTrue($set->hasValue(2));
        self::assertTrue($set->hasValue('3'));
        self::assertFalse($set->hasValue('5'));
        self::expectException(LogicException::class);
        $set->append(5);
    }

    /**
     * @test
     */
    public function it_should_remove_value_by_value(): void
    {
        /** @var MutableSet<mixed> $set */
        $set = MutableSet::fromArray(['a' => 1, 'b' => 3, 3 => 'a']);
        $set->remove('a');
        self::assertFalse($set->hasValue('2'));
        self::expectException(LogicException::class);
        $set->remove('a');
    }

    /**
     * @test
     */
    public function it_should_remove_value_by_unknown_object_value(): void
    {
        $baz = new Baz(1);
        $baz2 = new Baz(1);
        /** @var MutableSet<mixed> $set */
        $set = MutableSet::fromArray([$baz, 3, $baz]);
        self::assertCount(2, $set);

        $set->remove($baz2, false);
        self::assertCount(2, $set);

        $set->remove($baz, false);
        self::assertCount(1, $set);

        self::expectException(LogicException::class);
        $set->remove($baz2, true);
        self::assertCount(1, $set);
    }

    /**
     * @test
     */
    public function it_should_be_converted_as_immutable(): void
    {
        /** @var MutableSet<mixed> $set */
        $set = MutableSet::fromArray(['a', 'b', 3, 0, null]);
        $newMap = $set->toImmutable();
        self::assertSame($set->toArray(), $newMap->toArray());
    }

    /**
     * @test
     */
    public function it_should_filter_map(): void
    {
        /** @var MutableSet<mixed> $set */
        $set = MutableSet::fromArray(['a', 'b', 3, 0, null]);
        $set->filter(fn (mixed $value, mixed $index): bool => $index === 0 || $value === 'b');
        self::assertSame(['a', 'b'], $set->toArray());
    }

    /**
     * @test
     */
    public function it_should_slice_set(): void
    {
        /** @var MutableSet< mixed> $set */
        $set = MutableSet::fromArray(['a', 'b', 3, 0, null, 6, '8']);
        $set->slice(1, 3);
        self::assertSame(['b', 3, 0, null], $set->toArray());

        $set = MutableSet::fromArray(['a', 'b', 3, 0, null, 6, '8']);
        $set->slice(1);
        self::assertSame(['b', 3, 0, null, 6, '8'], $set->toArray());

        self::expectException(InvalidArgumentException::class);
        $set->slice(1, -1);
    }
}
