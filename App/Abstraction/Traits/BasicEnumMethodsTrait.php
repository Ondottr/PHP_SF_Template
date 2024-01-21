<?php declare( strict_types=1 );

namespace App\Abstraction\Traits;

use ReflectionClass;
use ReflectionClassConstant;

trait BasicEnumMethodsTrait
{

    /**
     * Return an instance of the enum by its ID.
     */
    abstract public static function getById( int $id ): self;

    /**
     * Return an ID of the enum.
     */
    abstract public function getId(): int;

    /**
     * Return a name of the enum.
     */
    abstract public function getName(): string;

    /**
     * Return a name code of the enum to save in the database, use in the URL, file name etc.
     */
    abstract public function getNameCode(): string;

    /**
     * Return a list of all enums.
     *
     * @return array<static>
     */
    final public function getList(): array
    {
        return ( new ReflectionClass( static::class ) )
            ->getConstants( ReflectionClassConstant::IS_PUBLIC );
    }

}