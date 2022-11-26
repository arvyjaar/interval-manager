<?php

declare(strict_types=1);

namespace Jaar\IntervalManager\Operation;

use Jaar\IntervalManager\Model\Interval;
use Jaar\IntervalManager\Model\IntervalCollection;
use Jaar\IntervalManager\Model\Point;

class UnionOperation extends AbstractOperation
{
    public function execute(
        IntervalCollection $intervals1,
        IntervalCollection $intervals2
    ): IntervalCollection {
        $this->setValidator->validateCollection($intervals1);
        $this->setValidator->validateCollection($intervals2);

        $intervalPoints = $this->collectIntervalPoints($intervals1, $intervals2);

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
                    ($beginningOfTheInterval->isInclusive()
                        ? Interval::INCLUSIVE_BEGINNING : 0) | ($point->isInclusive()
                        ? Interval::INCLUSIVE_END
                        : 0)
                );
                $result[]               = $newInterval;
                $beginningOfTheInterval = null;
            }
        }

        return new IntervalCollection(...$result);
    }

    /**
     * @param Point[] $intervalPoints
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
