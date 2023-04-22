<?php

declare(strict_types=1);

namespace Micoli\Multitude\Map\Operation;

/**
 * @template TKey
 * @template TValue
 *
 * @extends AbstractOperation<TKey, TValue>
 *
 * @inherits AbstractOperation<TKey, TValue>
 **/
class Sort extends AbstractOperation
{
    /**
     * @phan-suppress PhanGenericConstructorTypes
     **/
    public function __construct()
    {
    }

    /**
     * @param list<array{TKey, TValue}> $tuples
     * @param callable(TValue, TValue, TKey, TKey, int, int): int $sorter
     *
     * @return list<array{TKey, TValue}>
     */
    public function __invoke(array $tuples, callable $sorter): array
    {
        $temp = array_map(
            fn ($tuple, $index) => [$tuple[1], $tuple[0], $index],
            $tuples,
            array_keys($tuples),
        );
        uasort(
            $temp,
            /**
             * @param array{TValue, TKey, int} $valueA
             * @param array{TValue, TKey, int} $valueB
             */
            fn (array $valueA, array $valueB) => $sorter(
                $valueA[0],
                $valueB[0],
                $valueA[1],
                $valueB[1],
                $valueA[2],
                $valueB[2],
            ),
        );

        return array_values(array_map(
            fn (array $record) => [$record[1], $record[0]],
            $temp,
        ));
    }
}
