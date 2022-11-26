<?php

declare(strict_types=1);

namespace Jaar\IntervalManager\Operation;

use InvalidArgumentException;
use Jaar\IntervalManager\Model\IntervalCollection;

interface OperationOnCollectionsInterface
{
    /**
     * @throws InvalidArgumentException
     */
    public function execute(IntervalCollection $intervals1, IntervalCollection $intervals2): IntervalCollection;
}
