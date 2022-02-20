<?php

declare(strict_types=1);

namespace Jaar\IntervalUtils;

use Jaar\IntervalUtils\Model\IntervalCollection;
use InvalidArgumentException;

interface SetOperationInterface
{
    /**
     * @throws InvalidArgumentException
     */
    public function execute(IntervalCollection $intervals1, IntervalCollection $intervals2): IntervalCollection;
}
