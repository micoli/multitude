<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests\Set;

use Micoli\Multitude\Exception\EmptySetException;
use Micoli\Multitude\Exception\ValueAlreadyPresentException;
use Micoli\Multitude\Set\AbstractSet;
use Micoli\Multitude\Set\ImmutableSet;
use Micoli\Multitude\Set\MutableSet;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class SetTest extends TestCase
{
    /**
     * @return iterable<class-string, list<class-string<AbstractSet<mixed>>>>
     */
    public static function provideSetClass(): iterable
    {
        yield MutableSet::class => [MutableSet::class];
        yield ImmutableSet::class => [ImmutableSet::class];
    }

    /**
     * @test
     *
     * @dataProvider provideSetClass
     *
     * @param class-string<AbstractSet<mixed>> $className
     */
    public function it_should_instantiate_a_set(string $className): void
    {
        /** @var AbstractSet<mixed> $set */
        $set = new $className([1, 3, 4 => 'a']);
        self::assertSame([1, 3, 'a'], $set->toArray());
        self::assertSame([0, 1, 2], iterator_to_array($set->keys()));
        self::assertCount(3, $set);
        self::assertSame(3, $set->get(1));
        self::assertSame(666, $set->get(80, 666));
    }

    /**
     * @test
     *
     * @dataProvider provideSetClass
     *
     * @param class-string<AbstractSet<mixed>> $className
     */
    public function it_should_be_appended(string $className): void
    {
        /** @var AbstractSet<mixed> $set */
        $set = new $className([1, 3, 4 => 'a']);
        self::assertSame([1, 3, 'a', 'b'], $set->append('b')->toArray());

        /** @var AbstractSet<mixed> $set2 */
        $set2 = new $className([1, 3, 4 => 'a']);
        self::assertSame([1, 3, 'a', 'b'], $set2->append('b')->append('b', false)->toArray());

        /** @var AbstractSet<mixed> $set3 */
        $set3 = new $className([1, 3, 4 => 'a']);
        self::expectException(ValueAlreadyPresentException::class);
        self::assertSame([1, 3, 'a', 'b'], $set3->append('b')->append('b')->toArray());
    }

    /**
     * @test
     *
     * @dataProvider provideSetClass
     *
     * @param class-string<AbstractSet<mixed>> $className
     */
    public function it_should_be_counted(string $className): void
    {
        $set = new $className(['a', 'b', 3, 0, null]);
        self::assertCount(5, $set);
    }

    /**
     * @test
     *
     * @dataProvider provideSetClass
     *
     * @param class-string<AbstractSet<mixed>> $className
     */
    public function it_should_be_empty(string $className): void
    {
        self::assertTrue((new $className([]))->isEmpty());
        self::assertFalse((new $className([1]))->isEmpty());
    }

    /**
     * @test
     *
     * @dataProvider provideSetClass
     *
     * @param class-string<AbstractSet<mixed>> $className
     */
    public function it_should_be_iterated(string $className): void
    {
        /** @var AbstractSet<int|string|null> $set */
        $set = new $className(['a', 'b', 3, 0, null]);
        $result = [];
        foreach ($set as $v) {
            $result[] = $v;
        }
        self::assertSame(['a', 'b', 3, 0, null], $result);

        $result = [];
        foreach ($set->values() as $v) {
            $result[] = $v;
        }
        self::assertSame(['a', 'b', 3, 0, null], $result);
    }

    /**
     * @test
     *
     * @dataProvider provideSetClass
     *
     * @param class-string<AbstractSet<mixed>> $className
     */
    public function it_should_iterate_keys(string $className): void
    {
        $set = new $className(['a', 'b', 3, 0, null]);
        $keyList = '';
        foreach ($set->keys() as $k) {
            $keyList .= $k . ',';
        }
        self::assertSame('0,1,2,3,4,', $keyList);
    }

    /**
     * @test
     *
     * @dataProvider provideSetClass
     *
     * @param class-string<AbstractSet<mixed>> $className
     */
    public function it_should_get_first_element(string $className): void
    {
        $set = new $className(['a', 'b', 3, 0, null]);
        self::assertSame('a', $set->first());

        $set = new $className([]);
        self::assertSame(null, $set->first(false));

        $set = new $className([]);
        self::expectException(EmptySetException::class);
        $set->first();
    }

    /**
     * @test
     *
     * @dataProvider provideSetClass
     *
     * @param class-string<AbstractSet<mixed>> $className
     */
    public function it_should_get_last_element(string $className): void
    {
        $set = new $className(['a', 'b', 3, 0, null, 'last']);
        self::assertSame('last', $set->last());

        $set = new $className([]);
        self::assertSame(null, $set->last(false));

        $set = new $className([]);
        self::expectException(EmptySetException::class);
        $set->last();
    }

    /**
     * @test
     *
     * @dataProvider provideSetClass
     *
     * @param class-string<AbstractSet<mixed>> $className
     */
    public function it_should_map_values(string $className): void
    {
        $set = new $className(['a', 'b', 3, 0, null, 'last']);
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
     * @dataProvider provideSetClass
     *
     * @param class-string<AbstractSet<mixed>> $className
     */
    public function it_should_reduce_values(string $className): void
    {
        $set = new $className(['a', 'b', 3, 0, null, 'last']);
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
     * @dataProvider provideSetClass
     *
     * @param class-string<AbstractSet<mixed>> $className
     */
    public function it_should_reduce_to_a_single_value(string $className): void
    {
        /** @var AbstractSet<int> $set */
        $set = new $className([1 => 1, 2 => 2, 3 => 3]);
        $result = $set->reduce(function (int $accumulator, mixed $value, int $index): int {
            $accumulator += $value * ($index + 1);

            return $accumulator;
        }, 0);
        self::assertSame(14, $result);
    }

    /**
     * @test
     *
     * @dataProvider provideSetClass
     *
     * @param class-string<AbstractSet<mixed>> $className
     */
    public function it_should_use_foreach(string $className): void
    {
        $set = new $className([1, 3, 2, 4]);
        $result = [];
        $newSet = $set->foreach(function (mixed $value, int $index) use (&$result): bool {
            /** @var list<array{int, int}> $result */
            $result[] = [$index, $value];

            return $value !== 2;
        });
        self::assertInstanceOf($className, $newSet);
        self::assertSame([[0, 1], [1, 3], [2, 2]], $result);

        /** @var list<array{int, int}> $result */
        $result = [];
        $set->foreach(function (mixed $value, int $index) use (&$result): bool {
            /** @var list<array{int, int}> $result */
            $result[] = [$index, $value];

            return true;
        });
        self::assertSame([[0, 1], [1, 3], [2, 2], [3, 4]], $result);
    }
}
