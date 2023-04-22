<?php

declare(strict_types=1);

namespace Micoli\Multitude\Map\Operation;

/**
 * @template TKey
 * @template TValue
 **/
abstract class AbstractOperation
{
    /**
     * @param list<array{TKey, TValue}> $tuples
     * @param callable(TValue, TKey, int):bool $callable
     *
     * @return list<array{TKey, TValue}>
     */
    protected function filter(array $tuples, callable $callable): array
    {
        $result = [];

        foreach ($tuples as $index => [$key, $value]) {
            if (!$callable($value, $key, $index)) {
                continue;
            }
            $result[] = [$key, $value];
        }

        return $result;
    }
}
