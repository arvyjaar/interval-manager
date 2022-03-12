<?php

declare(strict_types=1);

namespace Jaar\IntervalManager;

use Jaar\IntervalManager\Model\IntervalCollection;
use Jaar\IntervalManager\Model\Point;

trait CanCollectIntervalPointsTrait
{
    /**
     * @param IntervalCollection ...$dateIntervalCollections
     * @return array<Point>
     */
    protected function collectIntervalPoints(IntervalCollection ...$dateIntervalCollections): array
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

        usort($intervalPoints, [$this, 'comparePoints']);

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
}
