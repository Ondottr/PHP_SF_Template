<?php /** @noinspection PhpMissingParentCallCommonInspection */
declare( strict_types=1 );
/*
 * Copyright © 2018-2024, Nations Original Sp. z o.o. <contact@nations-original.com>
 *
 * Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby
 * granted, provided that the above copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED \"AS IS\" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE
 * INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE
 * LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER
 * RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER
 * TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

namespace App\Command;

use App\Abstraction\Interfaces\CustomPurgerInterface;
use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\AbstractLogger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

/**
 * Class LoadDataFixturesDoctrineCommand
 *
 * Load data fixtures from bundles.
 *
 * @author  Dmytro Dyvulskyi <dmytro.dyvulskyi@nations-original.com>
 */
final class LoadDataFixturesDoctrineCommand extends DoctrineCommand
{

    private SymfonyFixturesLoader $fixturesLoader;

    /** @var CustomPurgerInterface[] */
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
            ->setName( 'doctrine:fixtures:custom-loader' )
            ->setDescription( 'Load data fixtures to your database' )
            ->addOption( 'force', 'f', InputOption::VALUE_NONE, 'Force loading fixtures without confirmation.' )
            ->addOption( 'em', null, InputOption::VALUE_REQUIRED, 'The entity manager to use for this command.' )
            ->addOption( 'group', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Only load fixtures that belong to this group' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int
    {
        $ui = new SymfonyStyle( $input, $output );

        if ( !$input->getOption( 'force' ) )
            if ( !$ui->confirm( sprintf( 'Careful, database "%s" will be purged. Do you want to continue?', em()->getConnection()->getDatabase() ), !$input->isInteractive() ) )
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

        $executor = new ORMExecutor( em() );
        $executor->setLogger( new class( $ui ) extends AbstractLogger {

            public function __construct( private readonly SymfonyStyle $ui ) {}

            public function log( $level, $message, array $context = [] ): void
            {
                $this->ui->text( sprintf( '  <comment>></comment> <info>%s</info>', $message ) );
            }

        } );

        // Purge outside the executor's transaction: TRUNCATE causes an implicit
        // commit in MySQL which would break the wrapInTransaction() used by execute().
        $ui->text( '  <comment>></comment> <info>purging database</info>' );

        foreach ( $this->purgers as $purger )
            $purger->purge();

        // append=true skips the built-in purge inside execute() — we already did it above.
        $executor->execute( $fixtures, true );

        return 0;
    }

}
