<?php

declare(strict_types=1);

namespace Micoli\Multitude\Set\Operation;

/**
 * @template TValue
 *
 * @extends AbstractOperation<TValue>
 *
 * @inherits AbstractOperation<TValue>
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
     * @param list<TValue> $values
     * @param callable(TValue, TValue, int, int): int $sorter
     *
     * @return list<TValue>
     */
    public function __invoke(array $values, callable $sorter): array
    {
        /** @var list<array{TValue, int}> $temp */
        $temp = array_map(
            fn ($value, $index) => [$value, $index],
            $values,
            array_keys($values),
        );
        uasort(
            $temp,
            /**
             * @param array{TValue, int} $valueA
             * @param array{TValue, int} $valueB
             */
            fn (array $valueA, array $valueB) => $sorter(
                $valueA[0],
                $valueB[0],
                $valueA[1],
                $valueB[1],
            ),
        );

        return array_values(array_map(
            fn (array $record) => $record[0],
            $temp,
        ));
    }
}
