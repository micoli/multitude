<?php

declare(strict_types=1);

namespace Micoli\Multitude\Set;

use Micoli\Multitude\MutableInterface;

/**
 * @template TValue
 *
 * @extends  AbstractSet<TValue>
 */
class MutableSet extends AbstractSet implements MutableInterface
{
    /**
     * @return ImmutableSet<TValue>
     */
    public function toImmutable(): ImmutableSet
    {
        /** @var ImmutableSet<TValue> */
        return new ImmutableSet($this->values);
    }
}
