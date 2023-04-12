<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests\Fixtures;

class Baz
{
    public function __construct(public readonly int $value)
    {
    }
}
