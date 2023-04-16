<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests\Fixtures;

use Micoli\Multitude\Map\ImmutableMap;

/**
 * @template TKey of string
 * @template TValue of Project
 *
 * @extends ImmutableMap<TKey, TValue>
 */
class Projects extends ImmutableMap
{
    /**
     * Add or replace a value in the map
     *
     * @param TKey $newKey
     * @param TValue $newValue
     */
    public function improvedSet(mixed $newKey, mixed $newValue): static
    {
        // do specific stuff, like logging or ther
        return $this->set($newKey, $newValue);
    }
}
