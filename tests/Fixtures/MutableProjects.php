<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests\Fixtures;

use Micoli\Multitude\Map\MutableMap;

/**
 * @extends MutableMap<string, Project>
 */
class MutableProjects extends MutableMap
{
    /**
     * Add or replace a value in the map
     */
    public function improvedSet(string $newKey, Project $newValue): static
    {
        // do specific stuff, like logging or other
        return $this->set($newKey, $newValue);
    }
}
