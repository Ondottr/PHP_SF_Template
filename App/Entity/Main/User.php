<?php declare( strict_types=1 );

namespace App\Entity\Main;

use App\Repository\Main\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use PHP_SF\Framework\Http\Middleware\auth;
use PHP_SF\System\Attributes\Validator\Constraints as Validate;
use PHP_SF\System\Attributes\Validator\TranslatablePropertyName;
use PHP_SF\System\Classes\Abstracts\AbstractEntity;
use PHP_SF\System\Interface\UserInterface;
use PHP_SF\System\Traits\ModelProperty\ModelPropertyCreatedAtTrait;

#[ORM\Entity( repositoryClass: UserRepository::class )]
#[ORM\Table( name: 'users', schema: 'php-sf-playground-db-mariadb' )]
#[ORM\Cache( usage: 'READ_WRITE' )]
class User extends AbstractEntity implements UserInterface
{
    use ModelPropertyCreatedAtTrait;


    #[Validate\Email]
    #[Validate\Length( min: 6, max: 50 )]
    #[TranslatablePropertyName( 'Email' )]
    #[ORM\Column( type: 'string', unique: true )]
    protected ?string $email = null;

    #[TranslatablePropertyName( 'Password' )]
    #[ORM\Column( type: 'string' )]
    protected ?string $password = null;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }


    public function setEmail( ?string $email ): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setPassword( ?string $password ): self
    {
        if ( $password !== null )
            $this->password = password_hash( $password, PASSWORD_BCRYPT );

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

}
