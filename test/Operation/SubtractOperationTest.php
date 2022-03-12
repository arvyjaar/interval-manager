<?php

declare(strict_types=1);

namespace Jaar\Test\IntervalManager\Operation;

use DateTime;
use InvalidArgumentException;
use Jaar\IntervalManager\Model\Interval;
use Jaar\IntervalManager\Model\IntervalCollection;
use Jaar\IntervalManager\Model\Value\CICharacterValue;
use Jaar\IntervalManager\Model\Value\DateTimeValue;
use Jaar\IntervalManager\Model\Value\FloatValue;
use Jaar\IntervalManager\Model\Value\IntegerValue;
use Jaar\IntervalManager\Operation\SubtractBinaryOperation;
use Jaar\IntervalManager\SetValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SubtractOperationTest extends TestCase
{
    /**
     * @var SetValidator|MockObject
     */
    protected SetValidator $validator;
    protected SubtractBinaryOperation $subtractOperation;

    public function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->getMockBuilder(SetValidator::class)->getMock();
        $this->subtractOperation = new SubtractBinaryOperation($this->validator);
    }

    /**
     * @test
     */
    public function validatorExceptionBubblesUp(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException());

        $this->validator->expects($this->any())
            ->method('validateCollection')
            ->with(new IntervalCollection())
            ->willThrowException(new InvalidArgumentException());

        $this->subtractOperation->execute(new IntervalCollection(), new IntervalCollection());
    }

    /**
     * @test
     */
    public function emptyIntervalSubtractionProduceEmptyResult(): void
    {
        $collection1 = new IntervalCollection();
        $collection2 = new IntervalCollection();

        $expectedResult = new IntervalCollection();

        $actualResult = $this->subtractOperation->execute($collection1, $collection2);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     *
     * <-empty->
     * X----------X
     * <-empty->
     */
    public function subtractingNonEmptyCollectionFromEmptyCollectionResultsInEmptyCollection(): void
    {
        $collection1 = new IntervalCollection();
        $collection2 = new IntervalCollection(
            new Interval(new DateTimeValue(new DateTime('2021-12-08 14:00:00')), new DateTimeValue(new DateTime('2021-12-08 18:00:00')))
        );

        $expectedResult = new IntervalCollection();

        $actualResult = $this->subtractOperation->execute($collection1, $collection2);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     *
     * X----------X
     * <-empty->
     * X----------X
     */
    public function subtractingEmptyCollectionFromNonEmptyCollectionResultsInNonEmptyCollection(): void
    {
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0))
            )
        );
        $collection2 = new IntervalCollection();

        $expectedResult = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0))
            )
        );

        $actualResult = $this->subtractOperation->execute($collection1, $collection2);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     *
     * X-----------X
     *       X-----------X
     * X------O
     */
    public function singleIntervalIsSubtractedCorrectly(): void
    {
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0))
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(14, 0))
            )
        );

        $expectedResult = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                Interval::INCLUSIVE_BEGINNING
            )
        );

        $actualResult = $this->subtractOperation->execute($collection1, $collection2);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     *
     * X-----X
     *           X-----X
     * X-----X
     */
    public function notOverlappingIntervalsAreSubtractedCorrectly(): void
    {
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0))
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(14, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(20, 0))
            )
        );

        $expectedResult = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0))
            )
        );

        $actualResult = $this->subtractOperation->execute($collection1, $collection2);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     *
     * X--------X     X----------X
     *       X-------------X
     * X-----O             O-----X
     */
    public function subtractionFromMultipleIntervalsWorksFine(): void
    {
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(15, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(20, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(5, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(18, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );

        $expectedResult = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(5, 0)),
                Interval::INCLUSIVE_BEGINNING
            ),
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(18, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(20, 0)),
                Interval::INCLUSIVE_END
            )
        );

        $actualResult = $this->subtractOperation->execute($collection1, $collection2);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     *
     * X--------X   X-----X
     *      O-----------------X
     * X----X
     */
    public function subtractedIntervalsEndPointIsTheLastOne(): void
    {
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(15, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(20, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(5, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(23, 0)),
                Interval::INCLUSIVE_END
            )
        );

        $expectedResult = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(5, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );

        $actualResult = $this->subtractOperation->execute($collection1, $collection2);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     * @dataProvider inclusivityWorksWellProvider
     */
    public function inclusivityWorksWell(
        IntervalCollection $collection1,
        IntervalCollection $collection2,
        IntervalCollection $expectedResult
    ): void {
        $actualResult = $this->subtractOperation->execute($collection1, $collection2);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     * @dataProvider subtractedIntervalIsLargerProvider
     */
    public function subtractedIntervalIsLarger(
        IntervalCollection $collection1,
        IntervalCollection $collection2,
        IntervalCollection $expectedResult
    ): void {
        $actualResult = $this->subtractOperation->execute($collection1, $collection2);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     * @dataProvider minuendIntervalOverlapsWithSubtrahendProvider
     */
    public function minuendIntervalOverlapsWithSubtrahend(
        IntervalCollection $collection1,
        IntervalCollection $collection2,
        IntervalCollection $expectedResult
    ): void {
        $actualResult = $this->subtractOperation->execute($collection1, $collection2);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     * @dataProvider subtractedIntervalIsSmallerProvider
     */
    public function subtractedIntervalIsSmaller(
        IntervalCollection $collection1,
        IntervalCollection $collection2,
        IntervalCollection $expectedResult
    ): void {
        $actualResult = $this->subtractOperation->execute($collection1, $collection2);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return iterable<IntervalCollection[]>
     */
    public function inclusivityWorksWellProvider(): iterable
    {
        /*
         * X---------X
         *           0----------X
         * X---------X
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(22, 0)),
                Interval::INCLUSIVE_END
            )
        );

        $expectedResult = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );

        yield [$collection1, $collection2, $expectedResult];

        /*
         *          X---------X
         * 0--------0
         *          X---------X
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(22, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                0
            )
        );

        $expectedResult = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(22, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );

        yield [$collection1, $collection2, $expectedResult];

        /*
         *          X---------X
         * 0--------X
         *          O---------X
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(22, 0, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_END
            )
        );

        $expectedResult = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(22, 0)),
                Interval::INCLUSIVE_END
            )
        );

        yield [$collection1, $collection2, $expectedResult];

        /*
         * X---------X
         * 0---------O
         * X         X
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(22, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(22, 0)),
                0
            )
        );

        $expectedResult = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(22, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(22, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );

        yield [$collection1, $collection2, $expectedResult];

        /*
         * X---------X
         * X---------X
         * <-empty->
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );

        $expectedResult = new IntervalCollection();

        yield [$collection1, $collection2, $expectedResult];

        /*
         * O---------O
         * X---------X
         * <-empty->
         */
        $collection1 = new IntervalCollection();
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );
        $expectedResult = new IntervalCollection();

        yield [$collection1, $collection2, $expectedResult];
    }

    /**
     * @return iterable<IntervalCollection[]>
     */
    public function subtractedIntervalIsLargerProvider(): iterable
    {
        /*
         *    0-----0
         * 0-----------0
         * <-empty->
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(4, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                0
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                0
            ),
        );
        $expected = new IntervalCollection();

        yield [$collection1, $collection2, $expected];

        /*
         * 0------0
         * 0----------0
         * <-empty->
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                0
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                0
            ),
        );
        $expected = new IntervalCollection();

        yield [$collection1, $collection2, $expected];

        /*
         * 0-----0
         * X----------0
         * <-empty->
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                0
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING
            ),
        );
        $expected = new IntervalCollection();

        yield [$collection1, $collection2, $expected];

        /*
         *      0------0
         * X-----------X
         * <-empty->
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(5, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                0
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_END | Interval::INCLUSIVE_BEGINNING
            ),
        );
        $expected = new IntervalCollection();

        yield [$collection1, $collection2, $expected];

        /*
         *      0-------0
         * X------------0
         * <-empty->
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(5, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                0
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING
            ),
        );
        $expected = new IntervalCollection();

        yield [$collection1, $collection2, $expected];
    }

    /**
     * @return iterable<IntervalCollection[]>
     */
    public function minuendIntervalOverlapsWithSubtrahendProvider(): iterable
    {
        /*
         * 0----------0
         *       0----------0
         * 0-----X
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(8, 0)),
                0
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(4, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                0
            ),
        );
        $expected = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(4, 0)),
                Interval::INCLUSIVE_END
            ),
        );

        yield [$collection1, $collection2, $expected];

        /*
         * 0------------0
         *         X----------0
         * 0-------0
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(8, 0)),
                0
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(4, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING
            ),
        );
        $expected = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(4, 0)),
                0
            ),
        );

        yield [$collection1, $collection2, $expected];

        /*
         * 0------------X
         *              X----------0
         * 0------------0
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(5, 0)),
                Interval::INCLUSIVE_END
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(5, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING
            ),
        );
        $expected = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(5, 0)),
                0
            ),
        );

        yield [$collection1, $collection2, $expected];

        /*
         *       0------------0
         * 0----------0
         *            X-------0
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(4, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                0
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                0
            ),
        );
        $expected = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING
            ),
        );

        yield [$collection1, $collection2, $expected];

        /*
         *         X----------0
         * 0------------X
         *              0-----0
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(4, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                Interval::INCLUSIVE_END
            ),
        );
        $expected = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                0
            ),
        );

        yield [$collection1, $collection2, $expected];

        /*
         *         X----------0
         * 0------------X
         *              0-----0
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new FloatValue(-10.05),
                new FloatValue(2.05),
                Interval::INCLUSIVE_BEGINNING
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new FloatValue(-20.05),
                new FloatValue(-6.05),
                Interval::INCLUSIVE_END
            ),
        );
        $expected = new IntervalCollection(
            new Interval(
                new FloatValue(-6.05),
                new FloatValue(2.05),
                0
            ),
        );

        yield [$collection1, $collection2, $expected];

        /*
         *         X--------X
         * X-------X
         *         0--------X
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(0, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
        );
        $expected = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_END
            ),
        );

        yield [$collection1, $collection2, $expected];

        /*        X--------X
        * X-------X
        *         0--------X
        */
        $collection1 = new IntervalCollection(
            new Interval(
                new IntegerValue(5),
                new IntegerValue(10),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new IntegerValue(0),
                new IntegerValue(5),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
        );
        $expected = new IntervalCollection(
            new Interval(
                new IntegerValue(5),
                new IntegerValue(10),
                Interval::INCLUSIVE_END
            ),
        );

        yield [$collection1, $collection2, $expected];
    }

    /**
     * @return iterable<IntervalCollection[]>
     */
    public function subtractedIntervalIsSmallerProvider(): iterable
    {
        /*
         * 0--------------------0
         *        0-----0
         * 0------X     X-------0
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(2, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                0
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(4, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                0
            ),
        );
        $expected = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(2, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(4, 0)),
                Interval::INCLUSIVE_END
            ),
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING
            ),
        );

        yield [$collection1, $collection2, $expected];

        /*
         * 0--------------------0
         *        X-----X
         * 0------0     0-------0
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(2, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                0
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(4, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
        );
        $expected = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(2, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(4, 0)),
                0
            ),
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(6, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                0
            ),
        );

        yield [$collection1, $collection2, $expected];

        /*
         * X--------------------X
         * 0--------------------0
         * XX                  XX
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(2, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(2, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                0
            ),
        );
        $expected = new IntervalCollection(
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(2, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(2, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
            new Interval(
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                new DateTimeValue((new DateTime('2021-12-08'))->setTime(10, 0)),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
        );

        yield [$collection1, $collection2, $expected];

        /*
         * X--------------------X
         * 0--------------------0
         * XX                  XX
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new FloatValue(0.2),
                new FloatValue(0.201),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new FloatValue(0.2),
                new FloatValue(0.201),
                0
            ),
        );
        $expected = new IntervalCollection(
            new Interval(
                new FloatValue(0.2),
                new FloatValue(0.2),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
            new Interval(
                new FloatValue(0.201),
                new FloatValue(0.201),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );

        yield [$collection1, $collection2, $expected];

        /*
         * X--------------------X
         * 0--------------------0
         * XX                  XX
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new IntegerValue(2),
                new IntegerValue(21),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new IntegerValue(2),
                new IntegerValue(21),
                0
            ),
        );
        $expected = new IntervalCollection(
            new Interval(
                new IntegerValue(2),
                new IntegerValue(2),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
            new Interval(
                new IntegerValue(21),
                new IntegerValue(21),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );

        yield [$collection1, $collection2, $expected];

        /*
         * X--------------------X
         * 0--------------------0
         * XX                  XX
         */
        $collection1 = new IntervalCollection(
            new Interval(
                new CICharacterValue('a'),
                new CICharacterValue('y'),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
        );
        $collection2 = new IntervalCollection(
            new Interval(
                new CICharacterValue('a'),
                new CICharacterValue('y'),
                0
            ),
        );
        $expected = new IntervalCollection(
            new Interval(
                new CICharacterValue('a'),
                new CICharacterValue('a'),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            ),
            new Interval(
                new CICharacterValue('y'),
                new CICharacterValue('y'),
                Interval::INCLUSIVE_BEGINNING | Interval::INCLUSIVE_END
            )
        );

        yield [$collection1, $collection2, $expected];
    }
}
