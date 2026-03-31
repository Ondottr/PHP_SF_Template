<?php declare( strict_types=1 );

namespace App\Command;

use Memcached;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


#[AsCommand(
    name: 'app:cache:clear',
    description: 'Clear application cache',
)]
final class AppCacheClearCommand extends Command
{

    private SymfonyStyle $io;


    /** @noinspection MissingParentCallInspection */
    protected function execute( InputInterface $input, OutputInterface $output ): int
    {
        $this->io = new SymfonyStyle( $input, $output );


        $this->io->text( 'Clearing cache...' );

        $this->clearAppCache();

        $this->io->success( 'All cache was successfully cleared.' );

        return Command::SUCCESS;
    }

    private function clearAppCache(): void
    {
        if ( function_exists( 'apcu_enabled' ) && apcu_enabled() )
            aca()->clear();

        if ( class_exists( Memcached::class ) )
            mca()->clear();

        rca()->clear();

        s()->clear();
    }

}
