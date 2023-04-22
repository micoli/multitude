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
class Filter extends AbstractOperation
{
    /**
     * @phan-suppress PhanGenericConstructorTypes
     **/
    public function __construct()
    {
    }

    /**
     * @param list<array{TKey, TValue}> $tuples
     * @param callable(TValue, TKey, int): bool $callback
     *
     * @return list<array{TKey, TValue}>
     */
    public function __invoke(array $tuples, callable $callback): array
    {
        $result = [];
        foreach ($tuples as $index => [$key, $value]) {
            if (!$callback($value, $key, $index)) {
                continue;
            }
            $result[] = [$key, $value];
        }

        return $result;
    }
}
