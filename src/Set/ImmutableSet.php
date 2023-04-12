<?php

declare(strict_types=1);

namespace Micoli\Multitude\Set;

use Micoli\Multitude\ImmutableInterface;

/**
 * @template TValue
 *
 * @extends  AbstractSet<TValue>
 */
class ImmutableSet extends AbstractSet implements ImmutableInterface
{
    /**
     * @return MutableSet<TValue>
     */
    public function toMutable(): MutableSet
    {
        return MutableSet::fromArray($this->values);
    }
}
