<?php

declare(strict_types=1);

namespace Jaar\IntervalManager\Model\Value;

use InvalidArgumentException;
use Jaar\IntervalManager\Model\ValueInterface;

class IntegerValue implements ValueInterface
{
    public function __construct(private readonly int $integer)
    {
    }

    public function isGreaterThan(ValueInterface $value): bool
    {
        if (!$value instanceof self) {
            throw new InvalidArgumentException();
        }

        return $this->integer > $value->integer;
    }

    public function isLessThan(ValueInterface $value): bool
    {
        if (!$value instanceof self) {
            throw new InvalidArgumentException();
        }

        return $this->integer < $value->integer;
    }
}
