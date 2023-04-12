<?php

declare(strict_types=1);

namespace Micoli\Multitude\Map;

use Micoli\Multitude\ImmutableInterface;

/**
 * @template TKey
 * @template TValue
 *
 * @extends  AbstractMap<TKey, TValue>
 */
class ImmutableMap extends AbstractMap implements ImmutableInterface
{
    /**
     * @return MutableMap<TKey, TValue>
     */
    public function toMutable(): MutableMap
    {
        return MutableMap::fromTuples($this->tuples);
    }
}
