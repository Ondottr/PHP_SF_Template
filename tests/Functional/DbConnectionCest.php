<?php declare( strict_types=1 );

namespace Tests\Functional;

use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use PHP_SF\System\Kernel as PhpSfKernel;
use PHPUnit\Framework\Assert;
use Tests\Support\FunctionalTester;

/**
 * Verifies that the test environment connects to the correct (_test) database
 * and that basic Doctrine operations work against it.
 *
 * All tests are skipped automatically when the database server is unreachable
 * or the schema has not been created yet.
 *
 * The entity manager is resolved via ManagerRegistry::getManagerForClass() so
 * the tests are not tied to any hardcoded connection name.
 */
class DbConnectionCest
{

    private ?int                   $createdEntityId = null;
    private EntityManagerInterface $em;


    // ── Lifecycle ────────────────────────────────────────────────────────────

    public function _before( FunctionalTester $I ): void
    {
        $userClass = PhpSfKernel::getApplicationUserClassName();

        /** @var EntityManagerInterface|null $em */
        $em = Kernel::getInstance()
            ->getContainer()
            ->get( 'doctrine' )
            ->getManagerForClass( $userClass );

        if ( $em === null )
            Assert::markTestSkipped( "No entity manager is configured for $userClass" );

        $this->em = $em;

        try {
            $this->em->getConnection()->executeQuery( 'SELECT 1' );
        } catch ( \Throwable $e ) {
            Assert::markTestSkipped( 'DB not reachable (is docker-compose up?): ' . $e->getMessage() );
        }

        $tableName = $this->em->getClassMetadata( $userClass )->getTableName();
        $tables    = $this->em->getConnection()->createSchemaManager()->listTableNames();

        if ( !in_array( $tableName, $tables, true ) )
            Assert::markTestSkipped(
                "Table '$tableName' not found — run: bin/console doctrine:schema:create --env=test"
            );
    }

    public function _after( FunctionalTester $I ): void
    {
        if ( $this->createdEntityId === null )
            return;

        $userClass = PhpSfKernel::getApplicationUserClassName();
        $entity    = $this->em->find( $userClass, $this->createdEntityId );

        if ( $entity !== null ) {
            $this->em->remove( $entity );
            $this->em->flush();
        }

        $this->createdEntityId = null;
    }


    // ── Connection ───────────────────────────────────────────────────────────

    public function testConnectionPointsToTestDatabase( FunctionalTester $I ): void
    {
        $dbName = $this->em->getConnection()->getDatabase();

        $I->assertStringEndsWith(
            '_test',
            $dbName,
            "Expected the test DB name to end with '_test', got: '$dbName'. " .
            'Is when\@test in doctrine.yaml applied?'
        );
    }

    public function testConnectionCanExecuteRawQuery( FunctionalTester $I ): void
    {
        $result = $this->em->getConnection()->fetchOne( 'SELECT DATABASE()' );

        $I->assertIsString( $result );
        $I->assertStringEndsWith( '_test', $result );
    }


    // ── ORM metadata ─────────────────────────────────────────────────────────

    public function testEntityManagerHasMappedEntities( FunctionalTester $I ): void
    {
        $classes = $this->em->getMetadataFactory()->getAllMetadata();

        $I->assertNotEmpty( $classes, 'No mapped entities found for this entity manager' );
    }

    public function testApplicationUserClassIsMapped( FunctionalTester $I ): void
    {
        $userClass = PhpSfKernel::getApplicationUserClassName();

        $I->assertTrue(
            $this->em->getMetadataFactory()->hasMetadataFor( $userClass ),
            "$userClass is not mapped to its entity manager"
        );
    }


    // ── Repository / CRUD ────────────────────────────────────────────────────

    public function testFindAllReturnsArray( FunctionalTester $I ): void
    {
        $userClass = PhpSfKernel::getApplicationUserClassName();
        $result    = $this->em->getRepository( $userClass )->findAll();

        $I->assertIsArray( $result );
    }

    public function testPersistAndRetrieve( FunctionalTester $I ): void
    {
        $userClass = PhpSfKernel::getApplicationUserClassName();
        $email     = 'integration-' . uniqid() . '@test.example';

        $entity = ( new $userClass() )
            ->setEmail( $email )
            ->setPassword( 'test-password' );

        $this->em->persist( $entity );
        $this->em->flush();

        $this->createdEntityId = $entity->getId();
        $I->assertNotNull( $this->createdEntityId );

        $this->em->clear();

        $found = $this->em->find( $userClass, $this->createdEntityId );
        $I->assertNotNull( $found );
        $I->assertEquals( $email, $found->getEmail() );
    }

    public function testRemove( FunctionalTester $I ): void
    {
        $userClass = PhpSfKernel::getApplicationUserClassName();
        $email     = 'integration-delete-' . uniqid() . '@test.example';

        $entity = ( new $userClass() )
            ->setEmail( $email )
            ->setPassword( 'test-password' );

        $this->em->persist( $entity );
        $this->em->flush();

        $id = $entity->getId();

        $this->em->remove( $entity );
        $this->em->flush();
        $this->em->clear();

        $I->assertNull( $this->em->find( $userClass, $id ) );
    }

}
