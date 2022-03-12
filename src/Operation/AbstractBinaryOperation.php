<?php

declare(strict_types=1);

namespace Jaar\IntervalManager\Operation;

use Jaar\IntervalManager\CanCollectIntervalPointsTrait;
use Jaar\IntervalManager\Model\Point;
use Jaar\IntervalManager\Model\ValueInterface;
use Jaar\IntervalManager\SetValidator;

abstract class AbstractBinaryOperation implements BinarySetOperationInterface
{
    use CanCollectIntervalPointsTrait;

    protected SetValidator $setValidator;

    public function __construct(SetValidator $setValidator)
    {
        $this->setValidator = $setValidator;
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
