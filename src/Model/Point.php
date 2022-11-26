<?php

declare(strict_types=1);

namespace Jaar\IntervalManager\Model;

class Point
{
    public function __construct(
        private readonly ValueInterface     $value,
        private readonly bool               $isBeginningPoint,
        private bool                        $isInclusive,
        private readonly IntervalCollection $collection,
        private readonly Interval           $interval
    ) {
    }

    public function getValue(): ValueInterface
    {
        return $this->value;
    }

    public function isBeginningPoint(): bool
    {
        return $this->isBeginningPoint;
    }

    public function isInclusive(): bool
    {
        return $this->isInclusive;
    }

    /**
     * @param bool $isInclusive
     */
    public function setIsInclusive(bool $isInclusive): void
    {
        $this->isInclusive = $isInclusive;
    }

    public function getCollection(): IntervalCollection
    {
        return $this->collection;
    }

    public function getInterval(): Interval
    {
        return $this->interval;
    }

    public function isEndPoint(): bool
    {
        return $this->isBeginningPoint === false;
    }
}
