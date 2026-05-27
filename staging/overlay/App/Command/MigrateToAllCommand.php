<?php declare( strict_types=1 );

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-to-all',
    description: 'Creates databases, schemas, and loads fixtures for all entity managers',
)]
final class MigrateToAllCommand extends Command
{

    private const array EMS = [ 'main', 'blog', 'payments' ];


    /** @noinspection MissingParentCallInspection */
    protected function execute( InputInterface $input, OutputInterface $output ): int
    {
        $io  = new SymfonyStyle( $input, $output );
        $app = $this->getApplication();

        foreach ( self::EMS as $em ) {
            $io->section( $em );

            $result = $app->find( 'doctrine:database:create' )->run(
                new ArrayInput( [ '--connection' => $em, '--if-not-exists' => true ] ),
                $output,
            );
            if ( $result !== Command::SUCCESS )
                return $result;

            $result = $app->find( 'doctrine:schema:create' )->run(
                new ArrayInput( [ '--em' => $em ] ),
                $output,
            );
            if ( $result !== Command::SUCCESS )
                return $result;

            $result = $app->find( 'doctrine:fixtures:custom-loader' )->run(
                new ArrayInput( [ '--em' => $em, '--group' => [ $em ], '--force' => true ] ),
                $output,
            );
            if ( $result !== Command::SUCCESS )
                return $result;
        }

        $io->success( 'All databases migrated and fixtures loaded.' );

        return Command::SUCCESS;
    }

}
