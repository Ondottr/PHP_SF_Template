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
    }

}
