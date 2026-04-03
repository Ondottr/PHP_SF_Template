<?php /** @noinspection PhpUnitAnnotationToAttributeInspection */
declare( strict_types=1 );

namespace Tests\Unit\Entity;

use App\Entity\Blog\Post;
use Codeception\Test\Unit;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use PHP_SF\System\Classes\Abstracts\AbstractEntity;
use ReflectionClass;
use ReflectionProperty;

/**
 * Unit tests for App\Entity\Blog\Post (MySQL connection).
 * Pure-unit: no database connection required.
 * Note: MySQL does not support datetimetz — that column is absent.
 */
class PostTest extends Unit
{

    private Post $post;


    protected function _before(): void
    {
        $this->post = new Post();
    }


    // -------------------------------------------------------------------------
    // Instantiation
    // -------------------------------------------------------------------------

    public function testImplementsAbstractEntity(): void
    {
        static::assertInstanceOf( AbstractEntity::class, $this->post );
    }

    public function testStaticConstructorReturnsPostInstance(): void
    {
        static::assertInstanceOf( Post::class, Post::new() );
    }

    public function testStaticConstructorReturnsNewInstanceEachTime(): void
    {
        static::assertNotSame( Post::new(), Post::new() );
    }


    // -------------------------------------------------------------------------
    // Constructor side-effects
    // -------------------------------------------------------------------------

    public function testConstructorSetsCreatedAt(): void
    {
        static::assertInstanceOf( DateTimeInterface::class, $this->post->getCreatedAt() );
    }

    public function testCreatedAtIsApproximatelyNow(): void
    {
        $diff = ( new DateTime() )->getTimestamp() - $this->post->getCreatedAt()->getTimestamp();
        static::assertLessThanOrEqual( 2, abs( $diff ) );
    }


    // -------------------------------------------------------------------------
    // title
    // -------------------------------------------------------------------------

    public function testTitleDefaultsToNull(): void
    {
        static::assertNull( $this->post->getTitle() );
    }

    public function testSetTitleReturnsSelf(): void
    {
        static::assertSame( $this->post, $this->post->setTitle( 'Hello World' ) );
    }

    public function testSetAndGetTitle(): void
    {
        $this->post->setTitle( 'Hello World' );
        static::assertEquals( 'Hello World', $this->post->getTitle() );
    }

    public function testSetTitleToNull(): void
    {
        $this->post->setTitle( 'Hello World' )->setTitle( null );
        static::assertNull( $this->post->getTitle() );
    }


    // -------------------------------------------------------------------------
    // content
    // -------------------------------------------------------------------------

    public function testContentDefaultsToNull(): void
    {
        static::assertNull( $this->post->getContent() );
    }

    public function testSetContentReturnsSelf(): void
    {
        static::assertSame( $this->post, $this->post->setContent( 'Body text.' ) );
    }

    public function testSetAndGetContent(): void
    {
        $this->post->setContent( 'Body text.' );
        static::assertEquals( 'Body text.', $this->post->getContent() );
    }

    public function testSetContentToNull(): void
    {
        $this->post->setContent( 'Body text.' )->setContent( null );
        static::assertNull( $this->post->getContent() );
    }


    // -------------------------------------------------------------------------
    // status
    // -------------------------------------------------------------------------

    public function testStatusDefaultsToDraft(): void
    {
        static::assertEquals( 'draft', $this->post->getStatus() );
    }

    public function testSetStatusReturnsSelf(): void
    {
        static::assertSame( $this->post, $this->post->setStatus( 'published' ) );
    }

    public function testSetAndGetStatus(): void
    {
        $this->post->setStatus( 'published' );
        static::assertEquals( 'published', $this->post->getStatus() );
    }


    // -------------------------------------------------------------------------
    // col* scalar properties — defaults, fluent setter, round-trip, null reset
    // -------------------------------------------------------------------------

    /**
     * @dataProvider scalarColDefaultNullProvider
     */
    public function testScalarColDefaultsToNull( string $getter ): void
    {
        static::assertNull( $this->post->$getter() );
    }

    public static function scalarColDefaultNullProvider(): array
    {
        return array_map(
            fn( string $g ) => [ $g ],
            [
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
        static::assertSame( $this->post, $this->post->$setter( $value ) );
    }

    /**
     * @dataProvider scalarColSetterProvider
     */
    public function testScalarColRoundTrip( string $setter, string $getter, mixed $value ): void
    {
        $this->post->$setter( $value );
        static::assertEquals( $value, $this->post->$getter() );
    }

    /**
     * @dataProvider scalarColSetterProvider
     */
    public function testScalarColResetToNull( string $setter, string $getter, mixed $value ): void
    {
        $this->post->$setter( $value );
        $this->post->$setter( null );
        static::assertNull( $this->post->$getter() );
    }

    public static function scalarColSetterProvider(): array
    {
        return [
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
        static::assertNull( $this->post->getColDate() );
    }

    public function testSetColDateReturnsSelf(): void
    {
        static::assertSame( $this->post, $this->post->setColDate( new DateTime() ) );
    }

    public function testSetAndGetColDate(): void
    {
        $dt = new DateTime( '2024-01-01' );
        $this->post->setColDate( $dt );
        static::assertSame( $dt, $this->post->getColDate() );
    }

    public function testSetColDateToNull(): void
    {
        $this->post->setColDate( new DateTime() )->setColDate( null );
        static::assertNull( $this->post->getColDate() );
    }

    public function testColTimeDefaultsToNull(): void
    {
        static::assertNull( $this->post->getColTime() );
    }

    public function testSetColTimeReturnsSelf(): void
    {
        static::assertSame( $this->post, $this->post->setColTime( new DateTime() ) );
    }

    public function testSetAndGetColTime(): void
    {
        $dt = new DateTime( '14:30:00' );
        $this->post->setColTime( $dt );
        static::assertSame( $dt, $this->post->getColTime() );
    }

    public function testSetColTimeToNull(): void
    {
        $this->post->setColTime( new DateTime() )->setColTime( null );
        static::assertNull( $this->post->getColTime() );
    }


    // -------------------------------------------------------------------------
    // col* mixed properties (blob, binary)
    // -------------------------------------------------------------------------

    public function testColBlobDefaultsToNull(): void
    {
        static::assertNull( $this->post->getColBlob() );
    }

    public function testSetColBlobReturnsSelf(): void
    {
        static::assertSame( $this->post, $this->post->setColBlob( 'binary content' ) );
    }

    public function testSetAndGetColBlob(): void
    {
        $this->post->setColBlob( 'binary content' );
        static::assertEquals( 'binary content', $this->post->getColBlob() );
    }

    public function testColBinaryDefaultsToNull(): void
    {
        static::assertNull( $this->post->getColBinary() );
    }

    public function testSetColBinaryReturnsSelf(): void
    {
        static::assertSame( $this->post, $this->post->setColBinary( "\x00\xFF\x10" ) );
    }

    public function testSetAndGetColBinary(): void
    {
        $this->post->setColBinary( "\x00\xFF\x10" );
        static::assertEquals( "\x00\xFF\x10", $this->post->getColBinary() );
    }

    // -------------------------------------------------------------------------
    // jsonSerialize
    // -------------------------------------------------------------------------

    public function testJsonSerializeReturnsArray(): void
    {
        ( new ReflectionProperty( Post::class, 'id' ) )->setValue( $this->post, 1 );
        static::assertIsArray( $this->post->jsonSerialize() );
    }

    public function testJsonSerializeContainsExpectedKeys(): void
    {
        ( new ReflectionProperty( Post::class, 'id' ) )->setValue( $this->post, 1 );

        $data = $this->post->jsonSerialize();

        foreach (
            [
                'title',
                'content',
                'status',
                'createdAt',
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
        ( new ReflectionProperty( Post::class, 'id' ) )->setValue( $this->post, 1 );

        $this->post
            ->setTitle( 'Test Post' )
            ->setStatus( 'published' )
            ->setColInteger( 99 )
            ->setColBoolean( true );

        $data = $this->post->jsonSerialize();
        static::assertEquals( 'Test Post', $data['title'] );
        static::assertEquals( 'published', $data['status'] );
        static::assertEquals( 99, $data['colInteger'] );
        static::assertTrue( $data['colBoolean'] );
    }


    // -------------------------------------------------------------------------
    // ORM column type attributes (pure reflection, no DB)
    // -------------------------------------------------------------------------

    /**
     * @dataProvider columnTypeProvider
     */
    public function testColumnTypes( string $property, string $expectedType ): void
    {
        $rp   = ( new ReflectionClass( Post::class ) )->getProperty( $property );
        $cols = $rp->getAttributes( Column::class );

        static::assertNotEmpty( $cols, "#[ORM\\Column] missing on Post::\$$property" );
        static::assertEquals( $expectedType, $cols[0]->newInstance()->type, "Wrong type on Post::\$$property" );
    }

    public static function columnTypeProvider(): array
    {
        return [
            [ 'title', 'string' ],
            [ 'content', 'text' ],
            [ 'status', 'string' ],
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
        $rp  = ( new ReflectionClass( Post::class ) )->getProperty( $property );
        $col = $rp->getAttributes( Column::class )[0]->newInstance();

        static::assertTrue( $col->nullable, "Column Post::\$$property should be nullable" );
    }

    public static function nullableColumnProvider(): array
    {
        return array_map(
            fn( string $p ) => [ $p ],
            [
                'content',
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
        $col = ( new ReflectionClass( Post::class ) )
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
            ( new ReflectionClass( Post::class ) )->getProperties()
        );
        static::assertNotContains( 'colDatetimetz', $properties, 'MySQL does not support datetimetz' );
    }

}
