<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests\Fixtures;

use Micoli\Multitude\Map\ImmutableMap;

/**
 * @extends ImmutableMap<string, Project>
 */
class Projects extends ImmutableMap
{
    /**
     * Add or replace a value in the map
     */
    public function improvedSet(string $newKey, Project $newValue): static
    {
        // do specific stuff, like logging or ther
        return $this->set($newKey, $newValue);
    }
}
