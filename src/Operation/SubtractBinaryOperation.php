<?php

declare(strict_types=1);

namespace Jaar\IntervalManager\Operation;

use Jaar\IntervalManager\Model\Interval;
use Jaar\IntervalManager\Model\IntervalCollection;
use Jaar\IntervalManager\Model\Point;

class SubtractBinaryOperation extends AbstractBinaryOperation implements BinarySetOperationInterface
{
    public function execute(IntervalCollection $intervals1, IntervalCollection $intervals2): IntervalCollection
    {
        $this->setValidator->validateCollection($intervals1);
        $this->setValidator->validateCollection($intervals2);

        $intervalPoints = $this->collectIntervalPoints($intervals1, $intervals2);

        $beginningOfResultInterval     = null;
        $beginningOfSubtrahendInterval = null;
        $result                        = [];

        foreach ($intervalPoints as $k => $point) {
            $nextPoint = $this->calculateNextPoint((int) $k, $intervalPoints);
            $beginningOfSubtrahendInterval = $this->getBeginningOfSubtrahendInterval($point, $intervals2, $beginningOfSubtrahendInterval);

            if ($this->isBeginningOfNewResultInterval($point, $nextPoint, $beginningOfResultInterval, $beginningOfSubtrahendInterval, $intervals1, $intervals2)) {
                $beginningOfResultInterval = new Point(
                    $point->getValue(),
                    true,
                    $this->isNewBeginningPointExclusive($point, $nextPoint, $intervals2),
                    $point->getCollection(),
                    $point->getInterval()
                );
            }

            if ($beginningOfResultInterval && $this->isTheEndOfResultInterval($point, $beginningOfResultInterval, $intervals1, $intervals2)) {
                $newInterval = new Interval(
                    $beginningOfResultInterval->getValue(),
                    $point->getValue(),
                    $this->calculateIntervalInclusiveness($beginningOfResultInterval, $point, $nextPoint, $intervals1, $intervals2)
                );

                $result[]                  = $newInterval;
                $beginningOfResultInterval = null;
            }
        }

        return new IntervalCollection(...$result);
    }

    private function isBeginningOfNewResultInterval(
        Point          $currentPoint,
        ?Point         $nextPoint,
        ?Point         $beginningOfResultInterval,
        ?Point         $beginningOfSubtrahendInterval,
        IntervalCollection $minuendIntervalCollection,
        IntervalCollection $subtrahendIntervalCollection
    ): bool {
        if ($beginningOfResultInterval === null && $nextPoint !== null
            && self::isEqual($currentPoint->getValue(), $nextPoint->getValue())
            && $nextPoint->getCollection() === $subtrahendIntervalCollection
            && $currentPoint->getCollection() === $minuendIntervalCollection) {
            return $currentPoint->isInclusive() && $nextPoint->isInclusive() === false;
        }

        if ($beginningOfSubtrahendInterval !== null) {
            return false;
        }

        if ($this->isEndOfSubtrahendInterval($currentPoint, $subtrahendIntervalCollection)) {
            return true;
        }

        return $beginningOfResultInterval === null;
    }

    private function isTheEndOfResultInterval(
        Point          $point,
        ?Point         $beginningOfTheInterval,
        IntervalCollection $intervals1,
        IntervalCollection $intervals2
    ): bool {
        if ($beginningOfTheInterval !== null && $point->isEndPoint() && $point->getCollection() === $intervals1) {
            return true;
        }

        return $point->isBeginningPoint() && $beginningOfTheInterval !== null && $point->getCollection() === $intervals2;
    }

    private function isNewBeginningPointExclusive(Point $point, ?Point $nextPoint, IntervalCollection $intervals2): bool
    {
        if ($nextPoint === null) {
            return $point->isInclusive();
        }

        if (self::isEqual($point->getValue(), $nextPoint->getValue()) === false) {
            return $point->isInclusive();
        }

        if ($nextPoint->getCollection() === $intervals2 && $nextPoint->isInclusive()) {
            return true;
        }

        return $point->isInclusive();
    }

    private function calculateIntervalInclusiveness(
        Point         $beginningOfTheInterval,
        Point          $endOfTheInterval,
        ?Point         $nextPoint,
        IntervalCollection $intervals1,
        IntervalCollection $intervals2
    ): int {
        $result = 0;

        if ($beginningOfTheInterval->isInclusive() && $beginningOfTheInterval->getCollection() === $intervals1) {
            $result |= Interval::INCLUSIVE_BEGINNING;
        }

        if ($beginningOfTheInterval->getCollection() === $intervals2) {
            if ($beginningOfTheInterval->isInclusive() === false) {
                $result |= Interval::INCLUSIVE_BEGINNING;
            }
        }

        if ($endOfTheInterval->isInclusive() && $endOfTheInterval->getCollection() === $intervals1) {
            if ($this->subtractedIntervalMakesEndExclusive($nextPoint, $endOfTheInterval, $intervals2) === false) {
                $result |= Interval::INCLUSIVE_END;
            }
        }

        if ($endOfTheInterval->getCollection() === $intervals2) {
            if ($endOfTheInterval->isInclusive() === false) {
                $result |= Interval::INCLUSIVE_END;
            }
        }

        return $result;
    }

    private function isBeginningOfNewSubtrahendInterval(Point $point, IntervalCollection $intervals2): bool
    {
        return $point->isBeginningPoint() && $point->getCollection() === $intervals2;
    }

    private function isEndOfSubtrahendInterval(Point $point, IntervalCollection $intervals2): bool
    {
        return $point->isBeginningPoint() === false && $point->getCollection() === $intervals2;
    }

    private function getBeginningOfSubtrahendInterval(
        Point          $point,
        IntervalCollection $intervals2,
        ?Point         $beginningOfSubtrahendInterval
    ): ?Point {
        if ($this->isEndOfSubtrahendInterval($point, $intervals2)) {
            $beginningOfSubtrahendInterval = null;
        }

        if ($this->isBeginningOfNewSubtrahendInterval($point, $intervals2)) {
            $beginningOfSubtrahendInterval = $point;
        }

        return $beginningOfSubtrahendInterval;
    }

    private function subtractedIntervalMakesEndExclusive(?Point $nextPoint, Point $endOfTheInterval, IntervalCollection $intervals2): bool
    {
        if ($nextPoint === null) {
            return false;
        }

        if (self::isEqual($endOfTheInterval->getValue(), $nextPoint->getValue()) === false) {
            return false;
        }

        if ($nextPoint->getCollection() !== $intervals2) {
            return false;
        }

        return $nextPoint->isInclusive();
    }
}
