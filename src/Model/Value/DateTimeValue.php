<?php

declare(strict_types=1);

namespace Jaar\IntervalManager\Model\Value;

use DateTimeInterface;
use InvalidArgumentException;
use Jaar\IntervalManager\Model\ValueInterface;

class DateTimeValue implements ValueInterface
{
    public function __construct(public readonly DateTimeInterface $dateTime)
    {
    }

    public function isGreaterThan(ValueInterface $value): bool
    {
        if (!$value instanceof self) {
            throw new InvalidArgumentException();
        }

        return $this->dateTime > $value->dateTime;
    }

    public function isLessThan(ValueInterface $value): bool
    {
        if (!$value instanceof self) {
            throw new InvalidArgumentException();
        }

        return $this->dateTime < $value->dateTime;
    }
}
