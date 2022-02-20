<?php

declare(strict_types=1);

namespace Jaar\Test\IntervalUtils\SetOperation;

use DateTime;
use InvalidArgumentException;
use Jaar\IntervalUtils\Model\Interval;
use Jaar\IntervalUtils\Model\IntervalCollection;
use Jaar\IntervalUtils\Model\Value\CICharacterValue;
use Jaar\IntervalUtils\Model\Value\DateTimeValue;
use Jaar\IntervalUtils\SetOperation\SubtractOperation;
use Jaar\IntervalUtils\SetOperation\UnionOperation;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    protected SubtractOperation $subtractOperation;
    protected UnionOperation $unionOperation;

    public function setUp(): void
    {
        parent::setUp();

        $this->subtractOperation = new SubtractOperation();
        $this->unionOperation = new UnionOperation();
    }

    /**
     * @dataProvider validationKicksInIfInputIntervalsOverlapProvider
     */
    public function testSubtractInputValidationWhenIntervalsOverlap(
        IntervalCollection $interval1,
        IntervalCollection $interval2
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Input intervals overlap!');

        $this->subtractOperation->execute($interval1, $interval2);
    }

    /**
     * @dataProvider validationKicksInIfInputIntervalsOverlapProvider
     */
    public function testUnionInputValidationWhenIntervalsOverlap(
        IntervalCollection $interval1,
        IntervalCollection $interval2
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Input intervals overlap!');

        $this->subtractOperation->execute($interval1, $interval2);
    }

    /**
     * @return array<IntervalCollection[]>
     */
    public function validationKicksInIfInputIntervalsOverlapProvider(): iterable
    {
        $interval1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                0
            ),
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(9, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(14, 0)),
                0
            )
        );
        $interval2 = new IntervalCollection();

        yield [$interval1, $interval2];
        yield [$interval2, $interval1];


        $interval1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                0
            ),
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(5, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                0
            )
        );
        $interval2 = new IntervalCollection();

        yield [$interval1, $interval2];
        yield [$interval2, $interval1];


        $interval1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                0
            ),
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                0
            )
        );
        $interval2 = new IntervalCollection();

        yield [$interval1, $interval2];
        yield [$interval2, $interval1];


        $interval1 = new IntervalCollection(
            new Interval(
                new CICharacterValue('A'),
                new CICharacterValue('C'),
                Interval::INCLUSIVE_END
            ),
            new Interval(
                new CICharacterValue('C'),
                new CICharacterValue('Z'),
                Interval::INCLUSIVE_BEGINNING
            )
        );
        $interval2 = new IntervalCollection();

        yield [$interval1, $interval2];
        yield [$interval2, $interval1];
    }
}
