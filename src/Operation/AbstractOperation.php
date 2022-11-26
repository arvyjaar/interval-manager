<?php

declare(strict_types=1);

namespace Jaar\IntervalManager\Operation;

use Jaar\IntervalManager\CanCollectIntervalPointsTrait;
use Jaar\IntervalManager\Model\IntervalCollection;
use Jaar\IntervalManager\Model\Point;
use Jaar\IntervalManager\Model\ValueInterface;
use Jaar\IntervalManager\SetValidator;

abstract class AbstractOperation implements OperationOnCollectionsInterface
{
    use CanCollectIntervalPointsTrait;

    public function __construct(protected readonly SetValidator $setValidator)
    {
    }

    abstract public function execute(
        IntervalCollection $intervals1,
        IntervalCollection $intervals2
    ): IntervalCollection;

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
