<?php /** @noinspection PhpMissingParentCallCommonInspection @noinspection UsingInclusionReturnValueInspection */
declare( strict_types=1 );

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


#[AsCommand(
    name: 'doctrine:fixtures:create-triggers',
)]
class DoctrineFixturesCreateTriggersCommand extends Command
{

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queries = require __DIR__ . '/../../config/doctrine_triggers.php';
        $io = new SymfonyStyle($input, $output);

        em()->beginTransaction();

        foreach ($queries as $q)
            em()
                ->getConnection()
                ->executeQuery(file_get_contents($q));

        em()->commit();

        $io->info('Triggers loaded');

        return Command::SUCCESS;
    }

}
