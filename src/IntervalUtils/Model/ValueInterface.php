<?php

declare(strict_types=1);

namespace Jaar\IntervalUtils\Model;

use InvalidArgumentException;

interface ValueInterface
{
    /**
     * @throws InvalidArgumentException
     */
    public function isGreaterThan(ValueInterface $value): bool;

    /**
     * @throws InvalidArgumentException
     */
    public function isLessThan(ValueInterface $value): bool;
}
