<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests\Fixtures;

class Project
{
    public function __construct(
        public readonly int $value,
        public readonly Tags $tags,
    ) {
    }
}
