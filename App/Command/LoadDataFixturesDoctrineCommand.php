<?php /** @noinspection PhpMissingParentCallCommonInspection */
/*
 * Copyright © 2018-2023, Nations Original Sp. z o.o. <contact@nations-original.com>
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

declare( strict_types=1 );

namespace App\Command;

use App\DataFixtures\Purgers\CustomPurgerInterface;
use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Bundle\FixturesBundle\Purger\ORMPurgerFactory;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function assert;
use function implode;
use function sprintf;


/**
 * Load data fixtures from bundles.
 */
final class LoadDataFixturesDoctrineCommand extends DoctrineCommand
{

    private SymfonyFixturesLoader $fixturesLoader;


    public function __construct( SymfonyFixturesLoader $fixturesLoader, ManagerRegistry|null $doctrine = null )
    {
        parent::__construct( $doctrine );

        $this->fixturesLoader = $fixturesLoader;
    }

    protected function configure(): void
    {
        $this
            ->setName( 'doctrine:fixtures:custom-loader' )
            ->setDescription( 'Load data fixtures to your database' )
            ->addOption( 'force', 'f', InputOption::VALUE_NONE, 'Force loading fixtures without confirmation.' )
            ->addOption( 'em', null, InputOption::VALUE_REQUIRED, 'The entity manager to use for this command.' )
            ->addOption( 'group', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Only load fixtures that belong to this group' )
            ->addOption( 'purge-exclusions', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'List of database tables to ignore while purging' )
            ->addOption( 'purge-with-truncate', null, InputOption::VALUE_NONE, 'Purge data by using a database-level TRUNCATE statement' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int
    {
        $ui = new SymfonyStyle( $input, $output );

        $em = $this->getDoctrine()->getManager( $input->getOption( 'em' ) );
        assert( $em instanceof EntityManagerInterface );

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

        $purgerFactories[] = new ORMPurgerFactory();

        foreach ( $purgerFactories as $purger ) {
            if ( $purger instanceof CustomPurgerInterface === false )
                $purger = $purger->createForEntityManager(
                    $input->getOption( 'em' ),
                    $em,
                    $input->getOption( 'purge-exclusions' ),
                    $input->getOption( 'purge-with-truncate' )
                );

            $executor = new ORMExecutor( $em, $purger );
            $executor->setLogger( function ( $message ) use ( $ui ): void {
                $ui->text( sprintf( '  <comment>></comment> <info>%s</info>', $message ) );
            } );
        }

        $executor->execute( $fixtures );

        $fixturesTriggersCommand = $this->getApplication()?->find( 'doctrine:fixtures:create-triggers' );
        $fixturesTriggersCommand->run( new ArrayInput( [] ), $output );

        return 0;
    }

}
