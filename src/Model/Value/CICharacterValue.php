<?php

declare(strict_types=1);

namespace Jaar\IntervalManager\Model\Value;

use InvalidArgumentException;
use Jaar\IntervalManager\Model\ValueInterface;

class CICharacterValue implements ValueInterface
{
    public function __construct(private readonly string $character)
    {
        if (preg_match('/^[A-Z]$/', $character) === false) {
            throw new InvalidArgumentException();
        }
    }

    public function isGreaterThan(ValueInterface $value): bool
    {
        if (!$value instanceof self) {
            throw new InvalidArgumentException();
        }

        return strtoupper($this->character) > strtoupper($value->character);
    }

    public function isLessThan(ValueInterface $value): bool
    {
        if (!$value instanceof self) {
            throw new InvalidArgumentException();
        }

        return strtoupper($this->character) < strtoupper($value->character);
    }
}
