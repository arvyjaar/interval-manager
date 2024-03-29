<?php

declare(strict_types=1);

namespace Jaar\IntervalManager;

use InvalidArgumentException;
use Jaar\IntervalManager\Model\IntervalCollection;
use Jaar\IntervalManager\Model\ValueInterface;

class SetValidator
{
    use CanCollectIntervalPointsTrait;

    /**
     * @throws InvalidArgumentException
     */
    public function validateCollection(IntervalCollection $collection): void
    {
        $intervalPoints = $this->collectIntervalPoints($collection);

        $beginningOfTheInterval = null;
        $previousPoint = null;

        foreach ($intervalPoints as $point) {
            if ($beginningOfTheInterval !== null && $beginningOfTheInterval->getInterval() !== $point->getInterval()) {
                throw new InvalidArgumentException('Input intervals overlap!');
            }

            if ($previousPoint !== null
                && $previousPoint->isInclusive()
                && $point->isInclusive()
                && self::isEqual($previousPoint->getValue(), $point->getValue())
            ) {
                throw new InvalidArgumentException('Input intervals overlap!');
            }

            $previousPoint = $point;

            if ($beginningOfTheInterval === null && $point->isBeginningPoint()) {
                $beginningOfTheInterval = $point;
            } elseif ($beginningOfTheInterval !== null && $point->isEndPoint()) {
                $beginningOfTheInterval = null;
            }
        }
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
