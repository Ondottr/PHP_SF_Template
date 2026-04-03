<?php declare( strict_types=1 );

namespace App\Maker;

use PHP_SF\System\Classes\Abstracts\AbstractEntityMaker;

final class MakePaymentsEntity extends AbstractEntityMaker
{

    protected string $entityNamespace     = 'App\Entity\Payments';
    protected string $repositoryNamespace = 'App\Repository\Payments';
    protected string $entityDir           = __DIR__ . '/../Entity/Payments';
    protected string $repositoryDir       = __DIR__ . '/../Repository/Payments';
    protected string $schema              = 'payments';

    public static function getCommandName(): string
    {
        return 'payments:make:entity';
    }

}
