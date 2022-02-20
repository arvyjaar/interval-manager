<?php

declare(strict_types=1);

namespace Jaar\IntervalUtils\Model;

class IntervalCollection
{
    /**
     * @var Interval[]
     */
    private array $intervals;

    public function __construct(Interval ...$intervals)
    {
        $this->intervals = $intervals;
    }

    /**
     * @return Interval[]
     */
    public function getIntervals(): array
    {
        return $this->intervals;
    }
}
