<?php /** @noinspection MethodShouldBeFinalInspection */
declare( strict_types=1 );
/*
 * Copyright © 2018-2024, Nations Original Sp. z o.o. <contact@nations-original.com>
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

namespace App\Entity;

use App\DoctrineLifecycleCallbacks\UserPreRemoveCallback;
use App\Enums\UserGroupEnum;
use App\Repository\UserRepository;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping as ORM;
use PHP_SF\Framework\Http\Middleware\auth;
use PHP_SF\System\Attributes\Validator\Constraints as Validate;
use PHP_SF\System\Attributes\Validator\TranslatablePropertyName;
use PHP_SF\System\Classes\Abstracts\AbstractEntity;
use PHP_SF\System\Core\DateTime;
use PHP_SF\System\Interface\UserInterface;
use PHP_SF\System\Traits\ModelProperty\ModelPropertyCreatedAtTrait;

use function is_int;

#[ORM\Entity( repositoryClass: UserRepository::class )]
#[ORM\Table( name: 'users' )]
#[ORM\Cache( usage: 'READ_WRITE' )]
#[ORM\Index( columns: [ 'email' ] )]
class User extends AbstractEntity implements UserInterface
{
    use ModelPropertyCreatedAtTrait;


    # region Basic properties
    #[Validate\Email]
    #[Validate\Length( min: 6, max: 50 )]
    #[TranslatablePropertyName( 'E-mail' )]
    #[ORM\Column( type: 'string', unique: true )]
    protected string $email;

    #[TranslatablePropertyName( 'Password' )]
    #[ORM\Column( type: 'string' )]
    protected string $password;
    # endregion


    # region ManyToOne properties
    #[TranslatablePropertyName( 'User Group' )]
    #[ORM\ManyToOne( targetEntity: UserGroup::class )]
    #[ORM\JoinColumn( name: 'user_group_id', nullable: false, columnDefinition: 'INT NOT NULL DEFAULT 6' )]
    protected int|UserGroup $userGroup;

    # endregion


    public function __construct()
    {
        $this->setCreatedAt( new DateTime );
    }


    # region Entity related methods
    public static function isAdmin( int|null $id = null ): bool
    {
        return self::userGroupCheck( UserGroupEnum::ADMINISTRATOR, $id );
    }

    private static function userGroupCheck( UserGroupEnum $userGroup, int|null $id = null ): bool
    {
        if ( $id !== null ) {
            if ( auth::isAuthenticated() && user()->getId() === $id )
                return user()->getUserGroup()->getId() === $userGroup->getId();

            if ( ( $user = self::find( $id ) ) instanceof self )
                return $user->getUserGroup()->getId() === $userGroup->getId();
        }

        return auth::isAuthenticated() && user()->getUserGroup()->getId() === $userGroup->getId();
    }
    # endregion


    # region Getters and Setters for Basic properties
    public function getEmail(): string|null
    {
        return $this->email;
    }

    public function setEmail( string|null $email ): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string|null
    {
        return $this->password;
    }

    public function setPassword( #[SensitiveParameter] string|null $password ): self
    {
        $this->password = password_hash( $password, PASSWORD_ARGON2I );

        return $this;
    }
    # endregion


    # region Getters and Setters for ManyToOne properties
    public function getUserGroup(): UserGroup
    {
        if ( is_int( $this->userGroup ) )
            $this->setUserGroup( UserGroup::find( $this->userGroup ) );

        return $this->userGroup;
    }

    public function setUserGroup( int|UserGroup $userGroup ): self
    {
        $this->userGroup = $userGroup;

        return $this;
    }
    # endregion


    public function getLifecycleCallbacks(): array
    {
        return [
            Events::postRemove => UserPreRemoveCallback::class,
        ];
    }

}
