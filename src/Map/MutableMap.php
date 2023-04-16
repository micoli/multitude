<?php

declare(strict_types=1);

namespace Micoli\Multitude\Map;

use Micoli\Multitude\MutableInterface;

/**
 * @template TKey
 * @template TValue
 *
 * @extends  AbstractMap<TKey, TValue>
 */
class MutableMap extends AbstractMap implements MutableInterface
{
    /**
     * @return ImmutableMap<TKey, TValue>
     */
    public function toImmutable(): ImmutableMap
    {
        /** @var ImmutableMap<TKey, TValue> */
        return new ImmutableMap($this->tuples);
    }
}
