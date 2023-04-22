<?php

declare(strict_types=1);

namespace Micoli\Multitude\Map\Operation;

use Micoli\Multitude\Map\AbstractMap;

/**
 * @template TKey
 * @template TValue
 *
 * @extends AbstractOperation<TKey, TValue>
 *
 * @inherits AbstractOperation<TKey, TValue>
 **/
class KeyDiff extends AbstractOperation
{
    /**
     * @phan-suppress PhanGenericConstructorTypes
     **/
    public function __construct()
    {
    }

    /**
     * @param list<array{TKey, TValue}> $tuples
     * @param AbstractMap<TKey, TValue> $compared
     *
     * @return list<array{TKey, TValue}>
     */
    public function __invoke(array $tuples, AbstractMap $compared): array
    {
        return $this->filter(
            $tuples,
            /**
             * @param TValue $value
             * @param TKey $key
             */
            fn (mixed $value, mixed $key, int $index) => !$compared->hasKey($key),
        );
    }
}
