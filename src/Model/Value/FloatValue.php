<?php

declare(strict_types=1);

namespace Jaar\IntervalManager\Model\Value;

use InvalidArgumentException;
use Jaar\IntervalManager\Model\ValueInterface;

class FloatValue implements ValueInterface
{
    public function __construct(private readonly float $float)
    {
    }

    public function isGreaterThan(ValueInterface $value): bool
    {
        if (!$value instanceof self) {
            throw new InvalidArgumentException();
        }

        return $this->float - $value->float > PHP_FLOAT_EPSILON;
    }

    public function isLessThan(ValueInterface $value): bool
    {
        if (!$value instanceof self) {
            throw new InvalidArgumentException();
        }

        return abs($this->float - $value->float) > PHP_FLOAT_EPSILON
            && $this->float - $value->float < PHP_FLOAT_EPSILON;
    }
}
