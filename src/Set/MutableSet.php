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
        return ImmutableSet::fromArray($this->values);
    }
}
