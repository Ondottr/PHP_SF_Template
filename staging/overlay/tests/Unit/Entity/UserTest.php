<?php /** @noinspection PhpUnitAnnotationToAttributeInspection */
declare( strict_types=1 );

namespace Tests\Unit\Entity;

use App\Entity\Main\User;
use Codeception\Test\Unit;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use PHP_SF\System\Classes\Abstracts\AbstractEntity;
use ReflectionClass;
use ReflectionProperty;

/**
 * Unit tests for App\Entity\Main\User (PostgreSQL connection).
 * Pure-unit: no database connection required.
 */
class UserTest extends Unit
{

    private User $user;


    protected function _before(): void
    {
        $this->user = new User();
    }


    // -------------------------------------------------------------------------
    // Instantiation
    // -------------------------------------------------------------------------

    public function testImplementsAbstractEntity(): void
    {
        static::assertInstanceOf( AbstractEntity::class, $this->user );
    }

    public function testStaticConstructorReturnsUserInstance(): void
    {
        static::assertInstanceOf( User::class, User::new() );
    }

    public function testStaticConstructorReturnsNewInstanceEachTime(): void
    {
        static::assertNotSame( User::new(), User::new() );
    }


    // -------------------------------------------------------------------------
    // Constructor side-effects
    // -------------------------------------------------------------------------

    public function testConstructorSetsCreatedAt(): void
    {
        static::assertInstanceOf( DateTimeInterface::class, $this->user->getCreatedAt() );
    }

    public function testCreatedAtIsApproximatelyNow(): void
    {
        $diff = ( new DateTime() )->getTimestamp() - $this->user->getCreatedAt()->getTimestamp();
        static::assertLessThanOrEqual( 2, abs( $diff ) );
    }


    // -------------------------------------------------------------------------
    // email
    // -------------------------------------------------------------------------

    public function testEmailDefaultsToNull(): void
    {
        static::assertNull( $this->user->getEmail() );
    }

    public function testSetEmailReturnsSelf(): void
    {
        static::assertSame( $this->user, $this->user->setEmail( 'user@example.com' ) );
    }

    public function testSetAndGetEmail(): void
    {
        $this->user->setEmail( 'user@example.com' );
        static::assertEquals( 'user@example.com', $this->user->getEmail() );
    }

    public function testSetEmailToNull(): void
    {
        $this->user->setEmail( 'user@example.com' )->setEmail( null );
        static::assertNull( $this->user->getEmail() );
    }


    // -------------------------------------------------------------------------
    // password
    // -------------------------------------------------------------------------

    public function testPasswordDefaultsToNull(): void
    {
        static::assertNull( $this->user->getPassword() );
    }

    public function testSetPasswordReturnsSelf(): void
    {
        static::assertSame( $this->user, $this->user->setPassword( 'secret123' ) );
    }

    public function testSetPasswordHashesWithBcrypt(): void
    {
        $this->user->setPassword( 'secret123' );
        static::assertStringStartsWith( '$2y$', $this->user->getPassword() );
        static::assertTrue( password_verify( 'secret123', $this->user->getPassword() ) );
    }

    public function testSetPasswordNullDoesNotChangeHash(): void
    {
        $this->user->setPassword( 'secret123' );
        $hash = $this->user->getPassword();
        $this->user->setPassword( null );
        static::assertEquals( $hash, $this->user->getPassword() );
    }

    public function testEachCallHashesUniquely(): void
    {
        $a = User::new()->setPassword( 'same' )->getPassword();
        $b = User::new()->setPassword( 'same' )->getPassword();
        static::assertNotEquals( $a, $b );
        static::assertTrue( password_verify( 'same', $a ) );
        static::assertTrue( password_verify( 'same', $b ) );
    }


    // -------------------------------------------------------------------------
    // col* scalar properties — defaults, fluent setter, round-trip, null reset
    // -------------------------------------------------------------------------

    /**
     * @dataProvider scalarColDefaultNullProvider
     */
    public function testScalarColDefaultsToNull(string $getter): void
    {
        static::assertNull($this->user->$getter());
    }

    public static function scalarColDefaultNullProvider(): array
    {
        return array_map(
            fn(string $g) => [$g],
            [
                'getColText', 'getColInteger', 'getColSmallint', 'getColBigint',
                'getColBoolean', 'getColDecimal', 'getColFloat', 'getColGuid',
                'getColJson', 'getColArray', 'getColSimpleArray',
            ]
        );
    }

    /**
     * @dataProvider scalarColSetterProvider
     */
    public function testScalarColSetterReturnsSelf( string $setter, string $getter, mixed $value ): void
    {
        static::assertSame( $this->user, $this->user->$setter( $value ) );
    }

    /**
     * @dataProvider scalarColSetterProvider
     */
    public function testScalarColRoundTrip( string $setter, string $getter, mixed $value ): void
    {
        $this->user->$setter( $value );
        static::assertEquals( $value, $this->user->$getter() );
    }

    /**
     * @dataProvider scalarColSetterProvider
     */
    public function testScalarColResetToNull( string $setter, string $getter, mixed $value ): void
    {
        $this->user->$setter( $value );
        $this->user->$setter( null );
        static::assertNull( $this->user->$getter() );
    }

    public static function scalarColSetterProvider(): array
    {
        return [
            [ 'setColText',        'getColText',        'hello world'                           ],
            [ 'setColInteger',     'getColInteger',     42                                      ],
            [ 'setColSmallint',    'getColSmallint',    32767                                   ],
            [ 'setColBigint',      'getColBigint',      '9223372036854775807'                   ],
            [ 'setColBoolean',     'getColBoolean',     true                                    ],
            [ 'setColDecimal',     'getColDecimal',     '9999.9999'                             ],
            [ 'setColFloat',       'getColFloat',       3.14159                                 ],
            [ 'setColGuid',        'getColGuid',        'f47ac10b-58cc-4372-a567-0e02b2c3d479' ],
            [ 'setColJson',        'getColJson',        [ 'key' => 'value', 'n' => 1 ]          ],
            [ 'setColArray',       'getColArray',       [ 'a', 'b', 'c' ]                       ],
            [ 'setColSimpleArray', 'getColSimpleArray', [ 'x', 'y', 'z' ]                       ],
        ];
    }


    // -------------------------------------------------------------------------
    // col* DateTime properties
    // -------------------------------------------------------------------------

    public function testColDatetimetzDefaultsToNull(): void
    {
        static::assertNull( $this->user->getColDatetimetz() );
    }

    public function testSetColDatetimetzReturnsSelf(): void
    {
        static::assertSame( $this->user, $this->user->setColDatetimetz( new DateTime() ) );
    }

    public function testSetAndGetColDatetimetz(): void
    {
        $dt = new DateTime( '2024-06-15 12:00:00' );
        $this->user->setColDatetimetz( $dt );
        static::assertSame( $dt, $this->user->getColDatetimetz() );
    }

    public function testSetColDatetimetzToNull(): void
    {
        $this->user->setColDatetimetz( new DateTime() )->setColDatetimetz( null );
        static::assertNull( $this->user->getColDatetimetz() );
    }

    public function testColDateDefaultsToNull(): void
    {
        static::assertNull( $this->user->getColDate() );
    }

    public function testSetColDateReturnsSelf(): void
    {
        static::assertSame( $this->user, $this->user->setColDate( new DateTime() ) );
    }

    public function testSetAndGetColDate(): void
    {
        $dt = new DateTime( '2024-01-01' );
        $this->user->setColDate( $dt );
        static::assertSame( $dt, $this->user->getColDate() );
    }

    public function testSetColDateToNull(): void
    {
        $this->user->setColDate( new DateTime() )->setColDate( null );
        static::assertNull( $this->user->getColDate() );
    }

    public function testColTimeDefaultsToNull(): void
    {
        static::assertNull( $this->user->getColTime() );
    }

    public function testSetColTimeReturnsSelf(): void
    {
        static::assertSame( $this->user, $this->user->setColTime( new DateTime() ) );
    }

    public function testSetAndGetColTime(): void
    {
        $dt = new DateTime( '14:30:00' );
        $this->user->setColTime( $dt );
        static::assertSame( $dt, $this->user->getColTime() );
    }

    public function testSetColTimeToNull(): void
    {
        $this->user->setColTime( new DateTime() )->setColTime( null );
        static::assertNull( $this->user->getColTime() );
    }


    // -------------------------------------------------------------------------
    // col* mixed properties (blob, binary)
    // -------------------------------------------------------------------------

    public function testColBlobDefaultsToNull(): void
    {
        static::assertNull( $this->user->getColBlob() );
    }

    public function testSetColBlobReturnsSelf(): void
    {
        static::assertSame( $this->user, $this->user->setColBlob( 'binary content' ) );
    }

    public function testSetAndGetColBlob(): void
    {
        $this->user->setColBlob( 'binary content' );
        static::assertEquals( 'binary content', $this->user->getColBlob() );
    }

    public function testColBinaryDefaultsToNull(): void
    {
        static::assertNull( $this->user->getColBinary() );
    }

    public function testSetColBinaryReturnsSelf(): void
    {
        static::assertSame( $this->user, $this->user->setColBinary( "\x00\xFF\x10" ) );
    }

    public function testSetAndGetColBinary(): void
    {
        $this->user->setColBinary( "\x00\xFF\x10" );
        static::assertEquals( "\x00\xFF\x10", $this->user->getColBinary() );
    }

    // -------------------------------------------------------------------------
    // jsonSerialize
    // -------------------------------------------------------------------------

    public function testJsonSerializeReturnsArray(): void
    {
        ( new ReflectionProperty( User::class, 'id' ) )->setValue( $this->user, 1 );
        static::assertIsArray( $this->user->jsonSerialize() );
    }

    public function testJsonSerializeContainsExpectedKeys(): void
    {
        ( new ReflectionProperty( User::class, 'id' ) )->setValue( $this->user, 1 );

        $data = $this->user->jsonSerialize();

        foreach ( [
            'email', 'password', 'createdAt',
            'colText', 'colInteger', 'colSmallint', 'colBigint',
            'colBoolean', 'colDecimal', 'colFloat',
            'colDatetimetz', 'colDate', 'colTime',
            'colJson', 'colBlob', 'colGuid',
            'colArray', 'colSimpleArray', 'colBinary',
        ] as $key ) {
            static::assertArrayHasKey( $key, $data, "Missing key '$key' in jsonSerialize output" );
        }
    }

    public function testJsonSerializeReflectsSetValues(): void
    {
        ( new ReflectionProperty( User::class, 'id' ) )->setValue( $this->user, 1 );

        $this->user
            ->setEmail( 'test@example.com' )
            ->setColText( 'some text' )
            ->setColInteger( 7 )
            ->setColBoolean( false );

        $data = $this->user->jsonSerialize();
        static::assertEquals( 'test@example.com', $data['email'] );
        static::assertEquals( 'some text', $data['colText'] );
        static::assertEquals( 7, $data['colInteger'] );
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
        $rp   = ( new ReflectionClass( User::class ) )->getProperty( $property );
        $cols = $rp->getAttributes( Column::class );

        static::assertNotEmpty( $cols, "#[ORM\\Column] missing on User::\$$property" );
        static::assertEquals( $expectedType, $cols[0]->newInstance()->type, "Wrong type on User::\$$property" );
    }

    public static function columnTypeProvider(): array
    {
        return [
            [ 'email',          'string'       ],
            [ 'password',       'string'       ],
            [ 'colText',        'text'         ],
            [ 'colInteger',     'integer'      ],
            [ 'colSmallint',    'smallint'     ],
            [ 'colBigint',      'bigint'       ],
            [ 'colBoolean',     'boolean'      ],
            [ 'colDecimal',     'decimal'      ],
            [ 'colFloat',       'float'        ],
            [ 'colDatetimetz',  'datetimetz'   ],
            [ 'colDate',        'date'         ],
            [ 'colTime',        'time'         ],
            [ 'colJson',        'json'         ],
            [ 'colBlob',        'blob'         ],
            [ 'colGuid',        'guid'         ],
            [ 'colArray',       'json'         ],
            [ 'colSimpleArray', 'simple_array' ],
            [ 'colBinary',      'binary'       ],
        ];
    }

    /**
     * @dataProvider nullableColumnProvider
     */
    public function testTypeColumnsAreNullable( string $property ): void
    {
        $rp  = ( new ReflectionClass( User::class ) )->getProperty( $property );
        $col = $rp->getAttributes( Column::class )[0]->newInstance();

        static::assertTrue( $col->nullable, "Column User::\$$property should be nullable" );
    }

    public static function nullableColumnProvider(): array
    {
        return array_map(
            fn( string $p ) => [ $p ],
            [
                'colText', 'colInteger', 'colSmallint', 'colBigint',
                'colBoolean', 'colDecimal', 'colFloat', 'colDatetimetz',
                'colDate', 'colTime', 'colJson', 'colBlob', 'colGuid',
                'colArray', 'colSimpleArray', 'colBinary',
            ]
        );
    }

    public function testColDecimalPrecisionAndScale(): void
    {
        $col = ( new ReflectionClass( User::class ) )
            ->getProperty( 'colDecimal' )
            ->getAttributes( Column::class )[0]
            ->newInstance();

        static::assertEquals( 15, $col->precision );
        static::assertEquals( 4, $col->scale );
    }

}
