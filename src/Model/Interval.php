<?php

declare(strict_types=1);

namespace Jaar\IntervalManager\Model;

class Interval
{
    /**
     * inclusive both = 0 (operator |)
     * inclusive none = 3 (operator &)
     */
    public const INCLUSIVE_BEGINNING = 1;
    public const INCLUSIVE_END       = 2;

    public function __construct(
        private readonly ValueInterface $beginning,
        private readonly ValueInterface $end,
        private readonly int $inclusiveness = self::INCLUSIVE_BEGINNING | self::INCLUSIVE_END
    ) {
    }

    public function getBeginning(): ValueInterface
    {
        return $this->beginning;
    }

    public function getEnd(): ValueInterface
    {
        return $this->end;
    }

    public function isBeginningInclusive(): bool
    {
        return ($this->inclusiveness & self::INCLUSIVE_BEGINNING) === self::INCLUSIVE_BEGINNING;
    }

    public function isEndInclusive(): bool
    {
        return ($this->inclusiveness & self::INCLUSIVE_END) === self::INCLUSIVE_END;
    }
}
