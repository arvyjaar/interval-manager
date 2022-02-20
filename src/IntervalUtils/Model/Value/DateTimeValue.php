<?php

declare(strict_types=1);

namespace Jaar\IntervalUtils\Model\Value;

use DateTimeInterface;
use InvalidArgumentException;
use Jaar\IntervalUtils\Model\ValueInterface;

class DateTimeValue implements ValueInterface
{
    private DateTimeInterface $dateTime;

    public function __construct(DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function isGreaterThan(ValueInterface $value): bool
    {
        if (!$value instanceof DateTimeValue) {
            throw new InvalidArgumentException();
        }

        return $this->dateTime > $value->getDateTime();
    }

    public function isLessThan(ValueInterface $value): bool
    {
        if (!$value instanceof DateTimeValue) {
            throw new InvalidArgumentException();
        }

        return $this->dateTime < $value->getDateTime();
    }

    public function getDateTime(): DateTimeInterface
    {
        return $this->dateTime;
    }
}
