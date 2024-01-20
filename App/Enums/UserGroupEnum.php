<?php declare( strict_types=1 );

namespace App\Enums;

use App\Abstraction\Traits\BasicEnumMethodsTrait;
use App\Entity\UserGroup;

/**
 * Class UserGroupEnum
 *
 * @package App\Enums
 * @author  Dmytro Dyvulskyi <dmytro.dyvulskyi@nations-original.com>
 */
enum UserGroupEnum: string
{
    use BasicEnumMethodsTrait;


    case BANNED = 'banned';
    case ADMINISTRATOR = 'administrator';
    case MODERATOR = 'moderator';
    case USER = 'user';


    public static function getById( int $id ): self
    {
        return match ( $id ) {
            -1 => self::BANNED,
            1  => self::ADMINISTRATOR,
            3  => self::MODERATOR,
            6  => self::USER
        };
    }

    public function getId(): int
    {
        return match ( $this ) {
            self::BANNED           => -1,
            self::ADMINISTRATOR    => 1,
            self::MODERATOR        => 3,
            self::USER             => 6
        };
    }

    public function getName(): string
    {
        return _t(
            match ( $this ) {
                self::BANNED           => 'Banned',
                self::ADMINISTRATOR    => 'Administrator',
                self::MODERATOR        => 'Moderator',
                self::USER             => 'User'
            }
        );
    }

    public function getNameCode(): string
    {
        return match ( $this ) {
            self::BANNED           => 'banned',
            self::ADMINISTRATOR    => 'administrator',
            self::MODERATOR        => 'moderator',
            self::USER             => 'user'
        };
    }

    public function getEntity(): UserGroup
    {
        return UserGroup::find( $this->getId() );
    }

}
