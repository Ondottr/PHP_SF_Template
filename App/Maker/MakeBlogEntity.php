<?php declare( strict_types=1 );

namespace App\Maker;

use PHP_SF\System\Classes\Abstracts\AbstractEntityMaker;

final class MakeBlogEntity extends AbstractEntityMaker
{

    protected string $entityNamespace     = 'App\Entity\Blog';
    protected string $repositoryNamespace = 'App\Repository\Blog';
    protected string $entityDir           = __DIR__ . '/../Entity/Blog';
    protected string $repositoryDir       = __DIR__ . '/../Repository/Blog';
    protected string $schema              = 'blog';

    public static function getCommandName(): string
    {
        return 'blog:make:entity';
    }

}
