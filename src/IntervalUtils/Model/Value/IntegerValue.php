<?php

declare(strict_types=1);

namespace Jaar\IntervalUtils\Model\Value;

use Jaar\IntervalUtils\Model\ValueInterface;
use InvalidArgumentException;

class IntegerValue implements ValueInterface
{
    private int $int;

    public function __construct(int $int)
    {
        $this->int = $int;
    }

    public function isGreaterThan(ValueInterface $value): bool
    {
        if (!$value instanceof IntegerValue) {
            throw new InvalidArgumentException();
        }

        return $this->int > $value->getInt();
    }

    public function isLessThan(ValueInterface $value): bool
    {
        if (!$value instanceof IntegerValue) {
            throw new InvalidArgumentException();
        }

        return $this->int < $value->getInt();
    }

    public function getInt(): int
    {
        return $this->int;
    }
}
