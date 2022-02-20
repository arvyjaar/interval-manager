<?php

declare(strict_types=1);

namespace Jaar\IntervalUtils\SetOperation;

use Jaar\IntervalUtils\Model\Interval;
use Jaar\IntervalUtils\Model\IntervalCollection;
use Jaar\IntervalUtils\Model\Point;
use Jaar\IntervalUtils\SetOperationInterface;

class UnionOperation extends AbstractOperation implements SetOperationInterface
{
    public function execute(
        IntervalCollection $intervals1,
        IntervalCollection $intervals2
    ): IntervalCollection {
        $this->validateCollection($intervals1);
        $this->validateCollection($intervals2);

        $intervalPoints = $this->composeIntervalPoints($intervals1, $intervals2);

        usort($intervalPoints, [$this, 'comparePoints']);

        $beginningOfTheInterval = null;
        $started                = 0;
        $finished               = 0;
        $result                 = [];

        foreach ($intervalPoints as $k => $point) {
            $point->isBeginningPoint() ? ++$started : ++$finished;

            if ($beginningOfTheInterval === null) {
                $beginningOfTheInterval = $point;

                // Handling interval beginning inclusivity
                $inclusivity = $this->determineInclusivity($intervalPoints, $k, false);
                if ($inclusivity) {
                    $beginningOfTheInterval->setIsInclusive(true);
                }
            }

            if ($started === $finished) {
                // Handling stitching point
                $inclusivity = $this->determineInclusivity($intervalPoints, $k, false);
                if ($inclusivity) {
                    continue;
                }

                // Handling interval end inclusivity
                $inclusivity = $this->determineInclusivity($intervalPoints, $k, true);
                if ($inclusivity) {
                    $point->setIsInclusive(true);
                }

                $newInterval = new Interval(
                    $beginningOfTheInterval->getValue(),
                    $point->getValue(),
                    ($beginningOfTheInterval->isInclusive() ? Interval::INCLUSIVE_BEGINNING : 0) | ($point->isInclusive() ? Interval::INCLUSIVE_END : 0)
                );
                $result[]               = $newInterval;
                $beginningOfTheInterval = null;
            }
        }

        return new IntervalCollection(...$result);
    }

    /**
     * @param array<Point> $intervalPoints
     */
    private function determineInclusivity(array $intervalPoints, int $k, bool $reverse): bool
    {
        $point = $intervalPoints[$k];
        $i     = 1;
        $key   = $reverse ? $k - 1 : $k + 1;

        while (isset($intervalPoints[$key]) && self::isEqual($intervalPoints[$key]->getValue(), $point->getValue())) {
            if ($intervalPoints[$key]->isInclusive() || $point->isInclusive()) {
                return true;
            }
            ++$i;
            $key = $reverse ? $k - $i : $k + $i;
        }

        return false;
    }
}
