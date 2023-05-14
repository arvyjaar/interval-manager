<?php

declare(strict_types=1);

namespace Jaar\Test\IntervalManager;

use DateTime;
use InvalidArgumentException;
use Jaar\IntervalManager\Model\Interval;
use Jaar\IntervalManager\Model\IntervalCollection;
use Jaar\IntervalManager\Model\Value\CICharacterValue;
use Jaar\IntervalManager\Model\Value\DateTimeValue;
use Jaar\IntervalManager\SetValidator;
use PHPUnit\Framework\TestCase;

class SetValidatorTest extends TestCase
{
    protected SetValidator $setValidator;

    public function setUp(): void
    {
        parent::setUp();

        $this->setValidator = new SetValidator();
    }

    /**
     * @dataProvider validationKicksInIfInputIntervalsOverlapProvider
     */
    public function testSubtractInputValidationWhenIntervalsOverlap(
        IntervalCollection $interval1
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Input intervals overlap!');

        $this->setValidator->validateCollection($interval1);
    }

    /**
     * @return array<IntervalCollection[]>
     */
    public static function validationKicksInIfInputIntervalsOverlapProvider(): iterable
    {
        $collection = new IntervalCollection(
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

        yield [$collection];


        $collection = new IntervalCollection(
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

        yield [$collection];


        $collection = new IntervalCollection(
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

        yield [$collection];


        $collection = new IntervalCollection(
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

        yield [$collection];
    }
}
