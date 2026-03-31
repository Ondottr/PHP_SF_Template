<?php /** @noinspection PhpMissingParentCallCommonInspection */
declare( strict_types=1 );

namespace App\Command;

use App\Abstraction\Classes\AbstractPurger;
use App\Abstraction\Interfaces\CustomPurgerInterface;
use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\AbstractLogger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

/**
 * Class LoadDataFixturesDoctrineCommand
 *
 * Load data fixtures to your database. Requires --em to specify which entity manager
 * (and therefore which database) to target.
 *
 */
#[AsCommand(
    name: 'doctrine:fixtures:custom-loader',
    description: 'Load data fixtures to your database',
)]
final class LoadDataFixturesDoctrineCommand extends DoctrineCommand
{

    private SymfonyFixturesLoader $fixturesLoader;

    /** @var AbstractPurger[] */
    private array $purgers;


    public function __construct(
        SymfonyFixturesLoader $fixturesLoader,
        #[TaggedIterator( 'app.fixture_purger' )] iterable $purgers,
        ManagerRegistry|null $doctrine = null
    ) {
        parent::__construct( $doctrine );

        $this->fixturesLoader = $fixturesLoader;
        $this->purgers        = iterator_to_array( $purgers );
    }

    protected function configure(): void
    {
        $this
            ->addOption( 'force', 'f', InputOption::VALUE_NONE, 'Force loading fixtures without confirmation.' )
            ->addOption( 'em', null, InputOption::VALUE_REQUIRED, 'The entity manager to use.' )
            ->addOption( 'group', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Only load fixtures that belong to this group' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int
    {
        $ui = new SymfonyStyle( $input, $output );

        $emName = $input->getOption( 'em' );
        if ( $emName === null ) {
            $available = implode( ', ', array_filter(
                array_keys( $this->getDoctrine()->getManagerNames() ),
                static fn( string $name ) => $name !== 'dummy'
            ) );
            $ui->error( sprintf( 'The --em option is required. Available entity managers: %s.', $available ) );

            return 1;
        }

        $em = $this->getEntityManager( $emName );

        if ( !$input->getOption( 'force' ) )
            if ( !$ui->confirm( sprintf( 'Careful, database "%s" will be purged. Do you want to continue?', $em->getConnection()->getDatabase() ), !$input->isInteractive() ) )
                return 0;

        $fixtures = $this->fixturesLoader
            ->getFixtures( $groups = $input->getOption( 'group' ) );

        if ( !$fixtures ) {
            $message = 'Could not find any fixture services to load';

            if ( !empty( $groups ) )
                $message .= sprintf( ' in the groups (%s)', implode( ', ', $groups ) );

            $ui->error( $message . '.' );

            return 1;
        }

        $executor = new ORMExecutor( $em );
        $executor->setLogger( new class( $ui ) extends AbstractLogger {

            public function __construct( private readonly SymfonyStyle $ui ) {}

            public function log( $level, $message, array $context = [] ): void
            {
                $this->ui->text( sprintf( '  <comment>></comment> <info>%s</info>', $message ) );
            }

        } );

        // Purge outside the executor's transaction: TRUNCATE/DELETE causes an implicit
        // commit in MySQL/MariaDB which would break the wrapInTransaction() used by execute().
        $ui->text( '  <comment>></comment> <info>purging database</info>' );

        foreach ( $this->purgers as $purger ) {
            if ( $purger->getEntityManagerName() !== $emName )
                continue;

            $purger->setEntityManager( $em );
            $purger->purge();
        }

        // append=true skips the built-in purge inside execute() — we already did it above.
        $executor->execute( $fixtures, true );

        return 0;
    }

}
