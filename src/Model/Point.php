<?php

declare(strict_types=1);

namespace Jaar\IntervalManager\Model;

class Point
{
    private ValueInterface $value;
    private bool $isBeginningPoint;
    private bool $isInclusive;
    private IntervalCollection $collection;
    private Interval $interval;

    public function __construct(
        ValueInterface  $value,
        bool               $isBeginningPoint,
        bool               $isInclusive,
        IntervalCollection $collection,
        Interval $interval
    ) {
        $this->value            = $value;
        $this->isBeginningPoint = $isBeginningPoint;
        $this->isInclusive      = $isInclusive;
        $this->collection       = $collection;
        $this->interval         = $interval;
    }

    public function getValue(): ValueInterface
    {
        return $this->value;
    }

    public function setValue(ValueInterface $value): void
    {
        $this->value = $value;
    }

    public function isBeginningPoint(): bool
    {
        return $this->isBeginningPoint;
    }

    public function isEndPoint(): bool
    {
        return $this->isBeginningPoint === false;
    }

    public function setIsBeginningPoint(bool $isBeginningPoint): void
    {
        $this->isBeginningPoint = $isBeginningPoint;
    }

    public function isInclusive(): bool
    {
        return $this->isInclusive;
    }

    public function setIsInclusive(bool $isInclusive): void
    {
        $this->isInclusive = $isInclusive;
    }

    public function getCollection(): IntervalCollection
    {
        return $this->collection;
    }

    public function setCollection(IntervalCollection $collection): void
    {
        $this->collection = $collection;
    }

    public function getInterval(): Interval
    {
        return $this->interval;
    }

    public function setInterval(Interval $interval): void
    {
        $this->interval = $interval;
    }
}
