<?php /** @noinspection PhpUnitAnnotationToAttributeInspection */
declare( strict_types=1 );

namespace Tests\Unit\Entity;

use App\Entity\Payments\Payment;
use Codeception\Test\Unit;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use PHP_SF\System\Classes\Abstracts\AbstractEntity;
use ReflectionClass;
use ReflectionProperty;

/**
 * Unit tests for App\Entity\Payments\Payment (MariaDB connection).
 * Pure-unit: no database connection required.
 * MariaDB column set mirrors MySQL — datetimetz is absent on both.
 */
class PaymentTest extends Unit
{

    private Payment $payment;


    protected function _before(): void
    {
        $this->payment = new Payment();
    }


    // -------------------------------------------------------------------------
    // Instantiation
    // -------------------------------------------------------------------------

    public function testImplementsAbstractEntity(): void
    {
        static::assertInstanceOf( AbstractEntity::class, $this->payment );
    }

    public function testStaticConstructorReturnsPaymentInstance(): void
    {
        static::assertInstanceOf( Payment::class, Payment::new() );
    }

    public function testStaticConstructorReturnsNewInstanceEachTime(): void
    {
        static::assertNotSame( Payment::new(), Payment::new() );
    }


    // -------------------------------------------------------------------------
    // Constructor side-effects
    // -------------------------------------------------------------------------

    public function testConstructorSetsCreatedAt(): void
    {
        static::assertInstanceOf( DateTimeInterface::class, $this->payment->getCreatedAt() );
    }

    public function testCreatedAtIsApproximatelyNow(): void
    {
        $diff = ( new DateTime() )->getTimestamp() - $this->payment->getCreatedAt()->getTimestamp();
        static::assertLessThanOrEqual( 2, abs( $diff ) );
    }


    // -------------------------------------------------------------------------
    // amount
    // -------------------------------------------------------------------------

    public function testAmountDefaultsToNull(): void
    {
        static::assertNull( $this->payment->getAmount() );
    }

    public function testSetAmountReturnsSelf(): void
    {
        static::assertSame( $this->payment, $this->payment->setAmount( '99.99' ) );
    }

    public function testSetAndGetAmount(): void
    {
        $this->payment->setAmount( '123.45' );
        static::assertEquals( '123.45', $this->payment->getAmount() );
    }

    public function testSetAmountToNull(): void
    {
        $this->payment->setAmount( '50.00' )->setAmount( null );
        static::assertNull( $this->payment->getAmount() );
    }

    public function testAmountColumnPrecisionAndScale(): void
    {
        $col = ( new ReflectionClass( Payment::class ) )
            ->getProperty( 'amount' )
            ->getAttributes( Column::class )[0]
            ->newInstance();

        static::assertEquals( 10, $col->precision );
        static::assertEquals( 2, $col->scale );
    }


    // -------------------------------------------------------------------------
    // currency
    // -------------------------------------------------------------------------

    public function testCurrencyDefaultsToUSD(): void
    {
        static::assertEquals( 'USD', $this->payment->getCurrency() );
    }

    public function testSetCurrencyReturnsSelf(): void
    {
        static::assertSame( $this->payment, $this->payment->setCurrency( 'EUR' ) );
    }

    public function testSetAndGetCurrency(): void
    {
        $this->payment->setCurrency( 'EUR' );
        static::assertEquals( 'EUR', $this->payment->getCurrency() );
    }

    public function testCurrencyColumnLength(): void
    {
        $col = ( new ReflectionClass( Payment::class ) )
            ->getProperty( 'currency' )
            ->getAttributes( Column::class )[0]
            ->newInstance();

        static::assertEquals( 3, $col->length );
    }


    // -------------------------------------------------------------------------
    // status
    // -------------------------------------------------------------------------

    public function testStatusDefaultsToPending(): void
    {
        static::assertEquals( 'pending', $this->payment->getStatus() );
    }

    public function testSetStatusReturnsSelf(): void
    {
        static::assertSame( $this->payment, $this->payment->setStatus( 'completed' ) );
    }

    public function testSetAndGetStatus(): void
    {
        $this->payment->setStatus( 'failed' );
        static::assertEquals( 'failed', $this->payment->getStatus() );
    }


    // -------------------------------------------------------------------------
    // col* scalar properties — defaults, fluent setter, round-trip, null reset
    // -------------------------------------------------------------------------

    /**
     * @dataProvider scalarColDefaultNullProvider
     */
    public function testScalarColDefaultsToNull( string $getter ): void
    {
        static::assertNull( $this->payment->$getter() );
    }

    public static function scalarColDefaultNullProvider(): array
    {
        return array_map(
            fn( string $g ) => [ $g ],
            [
                'getColText',
                'getColInteger',
                'getColSmallint',
                'getColBigint',
                'getColBoolean',
                'getColDecimal',
                'getColFloat',
                'getColGuid',
                'getColJson',
                'getColArray',
                'getColSimpleArray',
            ]
        );
    }

    /**
     * @dataProvider scalarColSetterProvider
     */
    public function testScalarColSetterReturnsSelf( string $setter, string $getter, mixed $value ): void
    {
        static::assertSame( $this->payment, $this->payment->$setter( $value ) );
    }

    /**
     * @dataProvider scalarColSetterProvider
     */
    public function testScalarColRoundTrip( string $setter, string $getter, mixed $value ): void
    {
        $this->payment->$setter( $value );
        static::assertEquals( $value, $this->payment->$getter() );
    }

    /**
     * @dataProvider scalarColSetterProvider
     */
    public function testScalarColResetToNull( string $setter, string $getter, mixed $value ): void
    {
        $this->payment->$setter( $value );
        $this->payment->$setter( null );
        static::assertNull( $this->payment->$getter() );
    }

    public static function scalarColSetterProvider(): array
    {
        return [
            [ 'setColText', 'getColText', 'hello world' ],
            [ 'setColInteger', 'getColInteger', 42 ],
            [ 'setColSmallint', 'getColSmallint', 32767 ],
            [ 'setColBigint', 'getColBigint', '9223372036854775807' ],
            [ 'setColBoolean', 'getColBoolean', true ],
            [ 'setColDecimal', 'getColDecimal', '9999.9999' ],
            [ 'setColFloat', 'getColFloat', 3.14159 ],
            [ 'setColGuid', 'getColGuid', 'f47ac10b-58cc-4372-a567-0e02b2c3d479' ],
            [ 'setColJson', 'getColJson', [ 'key' => 'value', 'n' => 1 ] ],
            [ 'setColArray', 'getColArray', [ 'a', 'b', 'c' ] ],
            [ 'setColSimpleArray', 'getColSimpleArray', [ 'x', 'y', 'z' ] ],
        ];
    }


    // -------------------------------------------------------------------------
    // col* DateTime properties
    // -------------------------------------------------------------------------

    public function testColDateDefaultsToNull(): void
    {
        static::assertNull( $this->payment->getColDate() );
    }

    public function testSetColDateReturnsSelf(): void
    {
        static::assertSame( $this->payment, $this->payment->setColDate( new DateTime() ) );
    }

    public function testSetAndGetColDate(): void
    {
        $dt = new DateTime( '2024-01-01' );
        $this->payment->setColDate( $dt );
        static::assertSame( $dt, $this->payment->getColDate() );
    }

    public function testSetColDateToNull(): void
    {
        $this->payment->setColDate( new DateTime() )->setColDate( null );
        static::assertNull( $this->payment->getColDate() );
    }

    public function testColTimeDefaultsToNull(): void
    {
        static::assertNull( $this->payment->getColTime() );
    }

    public function testSetColTimeReturnsSelf(): void
    {
        static::assertSame( $this->payment, $this->payment->setColTime( new DateTime() ) );
    }

    public function testSetAndGetColTime(): void
    {
        $dt = new DateTime( '14:30:00' );
        $this->payment->setColTime( $dt );
        static::assertSame( $dt, $this->payment->getColTime() );
    }

    public function testSetColTimeToNull(): void
    {
        $this->payment->setColTime( new DateTime() )->setColTime( null );
        static::assertNull( $this->payment->getColTime() );
    }


    // -------------------------------------------------------------------------
    // col* mixed properties (blob, binary)
    // -------------------------------------------------------------------------

    public function testColBlobDefaultsToNull(): void
    {
        static::assertNull( $this->payment->getColBlob() );
    }

    public function testSetColBlobReturnsSelf(): void
    {
        static::assertSame( $this->payment, $this->payment->setColBlob( 'binary content' ) );
    }

    public function testSetAndGetColBlob(): void
    {
        $this->payment->setColBlob( 'binary content' );
        static::assertEquals( 'binary content', $this->payment->getColBlob() );
    }

    public function testColBinaryDefaultsToNull(): void
    {
        static::assertNull( $this->payment->getColBinary() );
    }

    public function testSetColBinaryReturnsSelf(): void
    {
        static::assertSame( $this->payment, $this->payment->setColBinary( "\x00\xFF\x10" ) );
    }

    public function testSetAndGetColBinary(): void
    {
        $this->payment->setColBinary( "\x00\xFF\x10" );
        static::assertEquals( "\x00\xFF\x10", $this->payment->getColBinary() );
    }

    // -------------------------------------------------------------------------
    // jsonSerialize
    // -------------------------------------------------------------------------

    public function testJsonSerializeReturnsArray(): void
    {
        ( new ReflectionProperty( Payment::class, 'id' ) )->setValue( $this->payment, 1 );
        static::assertIsArray( $this->payment->jsonSerialize() );
    }

    public function testJsonSerializeContainsExpectedKeys(): void
    {
        ( new ReflectionProperty( Payment::class, 'id' ) )->setValue( $this->payment, 1 );

        $data = $this->payment->jsonSerialize();

        foreach (
            [
                'amount',
                'currency',
                'status',
                'createdAt',
                'colText',
                'colInteger',
                'colSmallint',
                'colBigint',
                'colBoolean',
                'colDecimal',
                'colFloat',
                'colDate',
                'colTime',
                'colJson',
                'colBlob',
                'colGuid',
                'colArray',
                'colSimpleArray',
                'colBinary',
            ] as $key
        ) {
            static::assertArrayHasKey( $key, $data, "Missing key '$key' in jsonSerialize output" );
        }
    }

    public function testJsonSerializeReflectsSetValues(): void
    {
        ( new ReflectionProperty( Payment::class, 'id' ) )->setValue( $this->payment, 7 );

        $this->payment
            ->setAmount( '250.00' )
            ->setCurrency( 'GBP' )
            ->setStatus( 'completed' )
            ->setColText( 'note' )
            ->setColBoolean( false );

        $data = $this->payment->jsonSerialize();
        static::assertEquals( '250.00', $data['amount'] );
        static::assertEquals( 'GBP', $data['currency'] );
        static::assertEquals( 'completed', $data['status'] );
        static::assertEquals( 'note', $data['colText'] );
        static::assertFalse( $data['colBoolean'] );
    }


    // -------------------------------------------------------------------------
    // ORM column type attributes (pure reflection, no DB)
    // -------------------------------------------------------------------------

    /**
     * @dataProvider columnTypeProvider
     */
    public function testColumnTypes( string $property, string $expectedType ): void
    {
        $rp   = ( new ReflectionClass( Payment::class ) )->getProperty( $property );
        $cols = $rp->getAttributes( Column::class );

        static::assertNotEmpty( $cols, "#[ORM\\Column] missing on Payment::\$$property" );
        static::assertEquals( $expectedType, $cols[0]->newInstance()->type, "Wrong type on Payment::\$$property" );
    }

    public static function columnTypeProvider(): array
    {
        return [
            [ 'amount', 'decimal' ],
            [ 'currency', 'string' ],
            [ 'status', 'string' ],
            [ 'colText', 'text' ],
            [ 'colInteger', 'integer' ],
            [ 'colSmallint', 'smallint' ],
            [ 'colBigint', 'bigint' ],
            [ 'colBoolean', 'boolean' ],
            [ 'colDecimal', 'decimal' ],
            [ 'colFloat', 'float' ],
            [ 'colDate', 'date' ],
            [ 'colTime', 'time' ],
            [ 'colJson', 'json' ],
            [ 'colBlob', 'blob' ],
            [ 'colGuid', 'guid' ],
            [ 'colArray', 'json' ],
            [ 'colSimpleArray', 'simple_array' ],
            [ 'colBinary', 'binary' ],
        ];
    }

    /**
     * @dataProvider nullableColumnProvider
     */
    public function testTypeColumnsAreNullable( string $property ): void
    {
        $rp  = ( new ReflectionClass( Payment::class ) )->getProperty( $property );
        $col = $rp->getAttributes( Column::class )[0]->newInstance();

        static::assertTrue( $col->nullable, "Column Payment::\$$property should be nullable" );
    }

    public static function nullableColumnProvider(): array
    {
        return array_map(
            fn( string $p ) => [ $p ],
            [
                'colText',
                'colInteger',
                'colSmallint',
                'colBigint',
                'colBoolean',
                'colDecimal',
                'colFloat',
                'colDate',
                'colTime',
                'colJson',
                'colBlob',
                'colGuid',
                'colArray',
                'colSimpleArray',
                'colBinary',
            ]
        );
    }

    public function testColDecimalPrecisionAndScale(): void
    {
        $col = ( new ReflectionClass( Payment::class ) )
            ->getProperty( 'colDecimal' )
            ->getAttributes( Column::class )[0]
            ->newInstance();

        static::assertEquals( 15, $col->precision );
        static::assertEquals( 4, $col->scale );
    }

    public function testNoDatetimetzColumn(): void
    {
        $properties = array_map(
            fn( $p ) => $p->getName(),
            ( new ReflectionClass( Payment::class ) )->getProperties()
        );
        static::assertNotContains( 'colDatetimetz', $properties, 'MariaDB does not support datetimetz' );
    }

}
