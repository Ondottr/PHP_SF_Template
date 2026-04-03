<?php declare( strict_types=1 );

namespace App\Maker;

use PHP_SF\System\Classes\Abstracts\AbstractEntityMaker;

final class MakeMainEntity extends AbstractEntityMaker
{

    protected string $entityNamespace     = 'App\Entity\Main';
    protected string $repositoryNamespace = 'App\Repository\Main';
    protected string $entityDir           = __DIR__ . '/../Entity/Main';
    protected string $repositoryDir       = __DIR__ . '/../Repository/Main';
    protected string $schema              = 'public';

    public static function getCommandName(): string
    {
        return 'main:make:entity';
    }

}
