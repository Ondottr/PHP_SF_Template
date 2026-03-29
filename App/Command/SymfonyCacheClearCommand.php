<?php declare( strict_types=1 );

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(
    name: 'symfony:cache:clear',
    description: 'Clear symfony cache',
)]
final class SymfonyCacheClearCommand extends Command
{

    protected function configure(): void {}


    protected function execute( InputInterface $input, OutputInterface $output ): int
    {
        $this->getApplication()
            ?->find( 'cache:clear' )
            ?->run( new ArrayInput( [] ), $output );

        $this->getApplication()
            ?->find( 'cache:pool:clear' )
            ?->run( new ArrayInput( [ 'pools' => [ 'cache.global_clearer' ] ] ), $output );

        $this->getApplication()
            ?->find( 'cache:warmup' )
            ?->run( new ArrayInput( [] ), $output );


        return Command::SUCCESS;
    }

}
