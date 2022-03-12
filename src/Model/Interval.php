<?php

namespace Jaar\IntervalManager\Model;

class Interval
{
    public const INCLUSIVE_BEGINNING = 1;
    public const INCLUSIVE_END       = 2;
    // inclusive both = 0 (operator |)
    // inclusive none = 3 (operator &)

    private ValueInterface $beginning;
    private ValueInterface $end;

    private int $inclusiveness;

    public function __construct(
        ValueInterface $beginning,
        ValueInterface $end,
        int $inclusiveness = self::INCLUSIVE_BEGINNING | self::INCLUSIVE_END
    ) {
        $this->beginning     = $beginning;
        $this->end           = $end;
        $this->inclusiveness = $inclusiveness;
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
