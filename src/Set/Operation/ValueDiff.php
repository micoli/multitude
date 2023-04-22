<?php

declare(strict_types=1);

namespace Micoli\Multitude\Set\Operation;

use Micoli\Multitude\Set\AbstractSet;

/**
 * @template TValue
 *
 * @extends AbstractOperation<TValue>
 *
 * @inherits AbstractOperation<TValue>
 **/
class ValueDiff extends AbstractOperation
{
    /**
     * @phan-suppress PhanGenericConstructorTypes
     **/
    public function __construct()
    {
    }

    /**
     * @param list<TValue> $values
     * @param AbstractSet<TValue> $compared
     *
     * @return list<TValue>
     */
    public function __invoke(array $values, AbstractSet $compared): array
    {
        return $this->filter(
            $values,
            /**
             * @param TValue $value
             */
            fn (mixed $value, int $index) => !$compared->hasValue($value),
        );
    }
}
