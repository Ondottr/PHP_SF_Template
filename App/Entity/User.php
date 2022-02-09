<?php declare( strict_types=1 );

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

namespace App\Entity;

use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping as ORM;
use PHP_SF\System\Interface\UserInterface;
use PHP_SF\Framework\Http\Middleware\auth;
use ApiPlatform\Core\Annotation\ApiResource;
use PHP_SF\System\Classes\Abstracts\AbstractEntity;
use App\DoctrineLifecycleCallbacks\UserPreRemoveCallback;
use PHP_SF\System\Attributes\Validator\Constraints as Validate;
use PHP_SF\System\Attributes\Validator\TranslatablePropertyName;
use PHP_SF\System\Traits\ModelProperty\ModelPropertyCreatedAtTrait;
use function is_int;


/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users", indexes={
 *     @ORM\Index(
 *          name="user_search_idx",
 *          columns={
 *              "id", "login", "password", "email", "user_group_id"
 *          }
 *     )
 * })
 */
#[ApiResource]
class User extends AbstractEntity implements UserInterface
{

    use ModelPropertyCreatedAtTrait;


    /**
     * @ORM\Column(type="string", unique=true)
     */
    #[Validate\Length( min: 2, max: 35 )]
    #[TranslatablePropertyName( 'user_login_property' )]
    protected ?string $login;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    #[Validate\Email]
    #[Validate\Length( min: 6, max: 50 )]
    #[TranslatablePropertyName( 'user_email_property' )]
    protected ?string $email;

    /**
     * @ORM\Column(type="string")
     */
    #[TranslatablePropertyName( 'user_password_property' )]
    protected ?string $password;


    /**
     * @ORM\ManyToOne(targetEntity=UserGroup::class, fetch="EAGER")
     * @ORM\JoinColumn(name="user_group_id", nullable=false, columnDefinition="INT NOT NULL DEFAULT 6")
     */
    #[TranslatablePropertyName( 'user_user_group_property' )]
    protected int|UserGroup $userGroup;


    public static function isAdmin( ?int $id = null ): bool
    {
        return self::userGroupCheck( UserGroup::ADMINISTRATOR, $id );
    }

    private static function userGroupCheck( int $userGroup, ?int $id = null ): bool
    {
        if ( $id !== null ) {

            if ( auth::isAuthenticated() && user()->getId() === $id )
                return user()
                           ->getUserGroup()
                           ->getId() === $userGroup;

            elseif ( ( $user = em()
                    ->getRepository( self::class )
                    ->find( $id ) ) instanceof self )
                return $user->getUserGroup()
                            ->getId() === $userGroup;

        }
        else
            return auth::isAuthenticated() &&
                   user()
                       ->getUserGroup()
                       ->getId() === $userGroup;

        return false;
    }

    public function getUserGroup(): UserGroup
    {
        if ( is_int( $this->userGroup ) )
            $this->setUserGroup(
                em()
                    ->getRepository( UserGroup::class )
                    ->find( $this->userGroup )
            );

        return $this->userGroup;
    }

    public function setUserGroup( int|UserGroup $userGroup ): void
    {
        $this->userGroup = $userGroup;
    }

    public static function isModerator( ?int $id = null ): bool
    {
        if ( self::userGroupCheck( UserGroup::ADMINISTRATOR, $id ) )
            return true;

        return self::userGroupCheck( UserGroup::MODERATOR, $id );
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword( ?string $password ): self
    {
        $this->password = password_hash( $password, PASSWORD_DEFAULT );

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin( ?string $login ): self
    {
        $this->login = $login;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail( ?string $email ): self
    {
        $this->email = $email;

        return $this;
    }


    public function getLifecycleCallbacks(): array
    {
        return [
            Events::preRemove => UserPreRemoveCallback::class,
        ];
    }
}
