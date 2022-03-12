<?php

declare(strict_types=1);

namespace Jaar\IntervalManager\Model\Value;

use InvalidArgumentException;
use Jaar\IntervalManager\Model\ValueInterface;

class FloatValue implements ValueInterface
{
    private float $float;

    public function __construct(float $float)
    {
        $this->float = $float;
    }

    public function isGreaterThan(ValueInterface $value): bool
    {
        if (!$value instanceof FloatValue) {
            throw new InvalidArgumentException();
        }

        return $this->float - $value->getFloat() > PHP_FLOAT_EPSILON;
    }

    public function isLessThan(ValueInterface $value): bool
    {
        if (!$value instanceof FloatValue) {
            throw new InvalidArgumentException();
        }

        return abs($this->float - $value->getFloat()) > PHP_FLOAT_EPSILON && $this->float - $value->getFloat() < PHP_FLOAT_EPSILON;
    }

    public function getFloat(): float
    {
        return $this->float;
    }
}
