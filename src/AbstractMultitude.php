<?php

declare(strict_types=1);

namespace Micoli\Multitude;

use Micoli\Multitude\Exception\InvalidArgumentException;

class AbstractMultitude
{
    protected function getSliceMax(int $count, int $offset, ?int $length = null): int
    {
        if ($offset < 0) {
            throw new InvalidArgumentException('Offset must be positive');
        }

        if ($length !== null && $length < 0) {
            throw new InvalidArgumentException('Length must be positive');
        }

        return $length === null ? $count : $offset + $length;
    }
}
