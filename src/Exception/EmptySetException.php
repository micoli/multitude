<?php

declare(strict_types=1);

namespace Micoli\Multitude\Exception;

use LogicException;

class EmptySetException extends LogicException implements MultitudeException
{
}
