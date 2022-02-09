<?php declare(strict_types=1);

/*
 * Copyright © 2018-2022, Nations Original Sp. z o.o. <contact@nations-original.com>
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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use function in_array;


#[AsCommand(
    name: 'app:cache:clear',
    description: 'Clear application cache',
)]
final class CacheClearCommand extends Command
{
    private const CACHE_ALL = 'all';
    private const CACHE_APP = 'app';
    private const CACHE_SYMFONY = 'symfony';
    private const CACHE_ROUTER = 'router';
    private const CACHE_TRANSLATIONS = 'translations';
    private const CACHE_TEMPLATES = 'templates';
    private const CACHE_AFCB = 'afcb';
    private const CACHE_APCU = 'apcu';

    private const CACHE_TYPE = [
        self::CACHE_ALL, self::CACHE_APP, self::CACHE_SYMFONY, self::CACHE_ROUTER, self::CACHE_TRANSLATIONS, self::CACHE_TEMPLATES, self::CACHE_AFCB, self::CACHE_APCU,
    ];

    private SymfonyStyle $io;


    protected function configure(): void
    {
        $this
            ->addArgument('cache_type', InputArgument::OPTIONAL, sprintf('Cache type which must be one of the following: `%s`.', implode('|', self::CACHE_TYPE)), self::CACHE_TYPE[0]);
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheType = $input->getArgument('cache_type');
        $this->io = new SymfonyStyle($input, $output);

        if (!in_array($cacheType, self::CACHE_TYPE, true))
            throw new InvalidArgumentException(
                sprintf('Undefined cache type specified, pick one of the following: `%s`',
                    implode('|', self::CACHE_TYPE)
                )
            );


        if ($input->getArgument('cache_type'))
            $this->io->text(sprintf('Clearing cache: %s', $cacheType));


        if ($cacheType === self::CACHE_ALL) {
            $this->clearAppCache();
            $this->clearSymfonyCache($output);

            $this->io->success('All cache was successfully cleared.');
        } elseif ($cacheType === self::CACHE_APP) {
            $this->clearAppCache();

            $this->io->success('Application cache was successfully cleared.');
        } else {
            if ($cacheType === self::CACHE_SYMFONY)
                $this->clearSymfonyCache($output);

            if ($cacheType === self::CACHE_ROUTER)
                $this->clearRouterCache();

            if ($cacheType === self::CACHE_TRANSLATIONS)
                $this->clearTranslationsCache();

            if ($cacheType === self::CACHE_TEMPLATES)
                $this->clearTemplatesCache();

            if ($cacheType === self::CACHE_AFCB)
                $this->clearAFCBCache();

            if ($cacheType === self::CACHE_APCU)
                $this->clearAPCuCache();
        }


        return Command::SUCCESS;
    }

    private function clearAppCache(): void
    {
        foreach (rc()->keys(SERVER_NAME . ':cache*') as $key)
            rp()->del($key);

        $this->clearRouterCache();
        $this->clearTranslationsCache();
        $this->clearTemplatesCache();
        $this->clearAFCBCache();
        $this->clearAPCuCache();
    }

    private function clearRouterCache(): void
    {
        rp()->del([
            SERVER_NAME . ':cache:routes_list',
            SERVER_NAME . ':cache:routes_by_url_list',
        ]);

        $this->io->success('Router cache cleared!');
    }

    private function clearTranslationsCache(): void
    {
        foreach (rc()->keys(SERVER_NAME . ':cache:translated_strings:*') as $key)
            rp()->del($key);

        $this->io->success('Translations cache cleared!');
    }

    private function clearTemplatesCache(): void
    {
        $dir = sprintf('/tmp/%s/PHP_SF/CachedTemplates', SERVER_NAME);

        if (is_dir($dir))
            exec(sprintf('rm -rf %s', escapeshellarg($dir)));

        $this->io->success('Cached templated cleared!');
    }

    private function clearAFCBCache(): void
    {
        $keys = rc()->keys(SERVER_NAME . ':cache:available_for_construction_buildings:*');

        foreach ($keys as $key)
            rp()->del($key);

        $this->io->success('Available for construction buildings cache cleared!');
    }

    private function clearAPCuCache(): void
    {
        apcu_clear_cache();

        $this->io->success('APCu cache cleared!');
    }

    private function clearSymfonyCache(OutputInterface $output): void
    {
        $fixturesTriggersCommand = $this->getApplication()?->find('cache:clear');
        $fixturesTriggersCommand->run(new ArrayInput([]), $output);
    }


}
