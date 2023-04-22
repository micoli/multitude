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
class Filter extends AbstractOperation
{
    /**
     * @phan-suppress PhanGenericConstructorTypes
     **/
    public function __construct()
    {
    }

    /**
     * @param list<TValue> $values
     * @param callable(TValue, int):bool $callback
     *
     * @return list<TValue>
     */
    public function __invoke(array $values, callable $callback): array
    {
        $result = [];
        foreach ($values as $index => $value) {
            if (!$callback($value, $index)) {
                continue;
            }
            $result[] = $value;
        }

        return $result;
    }
}
