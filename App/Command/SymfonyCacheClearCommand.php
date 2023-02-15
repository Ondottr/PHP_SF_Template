<?php declare( strict_types=1 );
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
