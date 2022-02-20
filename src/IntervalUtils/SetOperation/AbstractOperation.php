<?php

declare(strict_types=1);

namespace Jaar\IntervalUtils\SetOperation;

use Jaar\IntervalUtils\Model\IntervalCollection;
use Jaar\IntervalUtils\Model\Point;
use Jaar\IntervalUtils\Model\ValueInterface;
use Jaar\IntervalUtils\SetOperationInterface;
use InvalidArgumentException;

abstract class AbstractOperation implements SetOperationInterface
{
    /**
     * @throws InvalidArgumentException
     */
    protected function validateCollection(IntervalCollection $collection): void
    {
        $intervalPoints = $this->composeIntervalPoints($collection);

        usort($intervalPoints, [$this, 'comparePoints']);

        $beginningOfTheInterval = null;
        $previousPoint          = null;

        foreach ($intervalPoints as $point) {
            if ($beginningOfTheInterval !== null && $beginningOfTheInterval->getInterval() !== $point->getInterval()) {
                throw new InvalidArgumentException('Input intervals overlap!');
            }

            if ($previousPoint !== null && self::isEqual($previousPoint->getValue(), $point->getValue())
                && $previousPoint->isInclusive() && $point->isInclusive()) {
                throw new InvalidArgumentException('Input intervals overlap!');
            }

            $previousPoint = $point;

            if ($point->isBeginningPoint() && $beginningOfTheInterval === null) {
                $beginningOfTheInterval = $point;
            } elseif ($point->isEndPoint() && $beginningOfTheInterval !== null) {
                $beginningOfTheInterval = null;
            }
        }
    }

    /**
     * @param IntervalCollection ...$dateIntervalCollections
     * @return array<Point>
     */
    protected function composeIntervalPoints(IntervalCollection ...$dateIntervalCollections): array
    {
        $intervalPoints = [];

        foreach ($dateIntervalCollections as $intervalsCollection) {
            foreach ($intervalsCollection->getIntervals() as $interval) {
                $beginning = new Point(
                    clone $interval->getBeginning(),
                    true,
                    $interval->isBeginningInclusive(),
                    $intervalsCollection,
                    $interval
                );
                $end = new Point(
                    clone $interval->getEnd(),
                    false,
                    $interval->isEndInclusive(),
                    $intervalsCollection,
                    $interval
                );

                $intervalPoints[] = $beginning;
                $intervalPoints[] = $end;
            }
        }

        return $intervalPoints;
    }

    protected function comparePoints(Point $first, Point $second): int
    {
        if ($first->getValue()->isGreaterThan($second->getValue())) {
            return 1;
        }

        if ($first->getValue()->isLessThan($second->getValue())) {
            return -1;
        }

        return 0;
    }

    /**
     * @param array<Point> $intervalPoints
     */
    protected function calculateNextPoint(int $k, array $intervalPoints): ?Point
    {
        $nextIndex = $k + 1;

        if ($nextIndex >= count($intervalPoints)) {
            return null;
        }

        return $intervalPoints[$nextIndex];
    }

    protected static function isEqual(ValueInterface $value1, ValueInterface $value2): bool
    {
        if ($value1->isGreaterThan($value2)) {
            return false;
        }

        if ($value1->isLessThan($value2)) {
            return false;
        }

        return true;
    }
}
