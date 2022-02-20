<?php

declare(strict_types=1);

namespace Jaar\Test\IntervalUtils\SetOperation;

use Jaar\IntervalUtils\Model\Value\CICharacterValue;
use Jaar\IntervalUtils\Model\Value\DateTimeValue;
use Jaar\IntervalUtils\Model\Interval;
use Jaar\IntervalUtils\Model\IntervalCollection;
use Jaar\IntervalUtils\Model\Value\FloatValue;
use Jaar\IntervalUtils\Model\Value\IntegerValue;
use Jaar\IntervalUtils\SetOperation\UnionOperation;
use DateTime;
use PHPUnit\Framework\TestCase;

class UnionOperationTest extends TestCase
{
    protected UnionOperation $unionOperation;

    public function setUp(): void
    {
        parent::setUp();

        $this->unionOperation = new UnionOperation();
    }

    /**
     * @test
     * @dataProvider unionNonOverlappingProvider
     */
    public function unionNonOverlapping(
        IntervalCollection $collection1,
        IntervalCollection $collection2,
        IntervalCollection $expectedResult
    ): void {
        $actualResult = $this->unionOperation->execute($collection1, $collection2);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     * @dataProvider unionOverlappingProvider
     */
    public function unionOverlapping(
        IntervalCollection $collection1,
        IntervalCollection $collection2,
        IntervalCollection $expectedResult
    ): void {
        $actualResult = $this->unionOperation->execute($collection1, $collection2);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     * @dataProvider unionFullyCoveringProvider
     */
    public function unionFullyCovering(
        IntervalCollection $collection1,
        IntervalCollection $collection2,
        IntervalCollection $expectedResult
    ): void {
        $actualResult = $this->unionOperation->execute($collection1, $collection2);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return iterable<IntervalCollection[]>
     */
    public function unionNonOverlappingProvider(): iterable
    {
        /**
         * X-----X
         *          X-----X
         * X-----X  X-----X
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new CICharacterValue('A'),
                new CICharacterValue('G'),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new CICharacterValue('I'),
                new CICharacterValue('L'),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $expectedResult = new IntervalCollection(
            new Interval(
                new CICharacterValue('A'),
                new CICharacterValue('G'),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
            new Interval(
                new CICharacterValue('I'),
                new CICharacterValue('L'),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        yield [$collection1, $collection2, $expectedResult];

        /**
         * X-----0
         *       0-----X
         * X-----00----X
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new FloatValue(-0.1),
                new FloatValue(0.5),
                Interval::INCLUSIVE_BEGINNING
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new FloatValue(0.5),
                new FloatValue(1.0),
                Interval::INCLUSIVE_END
            )
        );
        $expectedResult = new IntervalCollection(
            new Interval(
                new FloatValue(-0.1),
                new FloatValue(0.5),
                Interval::INCLUSIVE_BEGINNING
            ),
            new Interval(
                new FloatValue(0.5),
                new FloatValue(1.0),
                Interval::INCLUSIVE_END
            )
        );
        yield [$collection1, $collection2, $expectedResult];

        /**
         * X-----X
         *       0-----X
         * X-----------X
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new FloatValue(-0.1),
                new FloatValue(0.5),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new FloatValue(0.5),
                new FloatValue(1.0),
                Interval::INCLUSIVE_END
            )
        );
        $expectedResult = new IntervalCollection(
            new Interval(
                new FloatValue(-0.1),
                new FloatValue(1.0),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        yield [$collection1, $collection2, $expectedResult];

        /**
         * X------X     0-----X
         *        0-----X
         * X------------------X
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new IntegerValue(-10),
                new IntegerValue(-5),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
            new Interval(
                new IntegerValue(5),
                new IntegerValue(10),
                Interval::INCLUSIVE_END
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new IntegerValue(-5),
                new IntegerValue(5),
                Interval::INCLUSIVE_END
            )
        );
        $expectedResult = new IntervalCollection(
            new Interval(
                new IntegerValue(-10),
                new IntegerValue(10),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        yield [$collection1, $collection2, $expectedResult];
    }

    /**
     * @return iterable<IntervalCollection[]>
     */
    public function unionOverlappingProvider(): iterable
    {
        /**
         * X-----------X
         *       X-----------X
         * X-----------------X
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(15, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(20, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $expectedResult = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(20, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        yield [$collection1, $collection2, $expectedResult];

        /**
         * X-------X     X-----X
         *      X----------X
         * X-------------------X
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(15, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(23, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(5, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(20, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $expectedResult = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(23, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        yield [$collection1, $collection2, $expectedResult];

        /**
         *       X-----------X
         * X-----------X
         * X-----------------X
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new CICharacterValue('G'),
                new CICharacterValue('L'),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new CICharacterValue('A'),
                new CICharacterValue('I'),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $expectedResult = new IntervalCollection(
            new Interval(
                new CICharacterValue('A'),
                new CICharacterValue('L'),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        yield [$collection1, $collection2, $expectedResult];
    }

    /**
     * @return iterable<IntervalCollection[]>
     */
    public function unionFullyCoveringProvider(): iterable
    {
        /**
         * X-------------X
         *    X------X
         * X-------------X
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(15, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(3, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(7, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $expectedResult = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(15, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        yield [$collection1, $collection2, $expectedResult];

        /**
         *    X-----X
         * X-----------X
         * X-----------X
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(3, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(7, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(15, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $expectedResult = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(15, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        yield [$collection1, $collection2, $expectedResult];
    }
}
