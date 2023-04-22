<?php

declare(strict_types=1);

namespace Micoli\Multitude\Set\Operation;

/**
 * @template TValue
 **/
abstract class AbstractOperation
{
    /**
     * @param list<TValue> $values
     * @param callable(TValue, int):bool $callable
     *
     * @return list<TValue>
     */
    protected function filter(array $values, callable $callable): array
    {
        $result = [];

        foreach ($values as $index => $value) {
            if (!$callable($value, $index)) {
                continue;
            }
            $result[] = $value;
        }

        return $result;
    }
}
