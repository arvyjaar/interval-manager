<?php

declare(strict_types=1);

namespace Jaar\IntervalUtils\Model\Value;

use InvalidArgumentException;
use Jaar\IntervalUtils\Model\ValueInterface;

class CICharacterValue implements ValueInterface
{
    private string $char;

    public function __construct(string $char)
    {
        if (preg_match('/^[A-Z]$/', $char) === false) {
            throw new InvalidArgumentException();
        }

        $this->char = $char;
    }

    public function isGreaterThan(ValueInterface $value): bool
    {
        if (!$value instanceof CICharacterValue) {
            throw new InvalidArgumentException();
        }

        return strtoupper($this->char) > strtoupper($value->getChar());
    }

    public function isLessThan(ValueInterface $value): bool
    {
        if (!$value instanceof CICharacterValue) {
            throw new InvalidArgumentException();
        }

        return strtoupper($this->char) < strtoupper($value->getChar());
    }

    public function getChar(): string
    {
        return $this->char;
    }
}
