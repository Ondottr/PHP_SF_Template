<?php declare( strict_types=1 );

namespace App\Entity\Main;

use App\Repository\Main\UserRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use PHP_SF\System\Attributes\Validator\TranslatablePropertyName;
use PHP_SF\System\Classes\Abstracts\AbstractEntity;
use PHP_SF\System\Interface\UserInterface;
use PHP_SF\System\Traits\ModelProperty\ModelPropertyCreatedAtTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity( repositoryClass: UserRepository::class )]
#[ORM\Table( name: 'users', schema: 'public' )]
#[ORM\Cache( usage: 'READ_WRITE' )]
class User extends AbstractEntity implements UserInterface
{
    use ModelPropertyCreatedAtTrait;


    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length( min: 6, max: 50 )]
    #[TranslatablePropertyName( 'Email' )]
    #[ORM\Column( type: 'string', unique: true )]
    protected ?string $email = null;

    #[Assert\NotBlank]
    #[TranslatablePropertyName( 'Password' )]
    #[ORM\Column( type: 'string' )]
    protected ?string $password = null;

    // --- PostgreSQL type coverage ---

    #[TranslatablePropertyName( 'Text' )]
    #[ORM\Column( type: 'text', nullable: true )]
    protected ?string $colText = null;

    #[Assert\Range( min: -2147483648, max: 2147483647 )]
    #[TranslatablePropertyName( 'Integer' )]
    #[ORM\Column( type: 'integer', nullable: true )]
    protected ?int $colInteger = null;

    #[Assert\Range( min: -32768, max: 32767 )]
    #[TranslatablePropertyName( 'Small Integer' )]
    #[ORM\Column( type: 'smallint', nullable: true )]
    protected ?int $colSmallint = null;

    /** @var int|string|null Doctrine returns bigint as string to avoid overflow */
    #[Assert\Regex( pattern: '/^-?\d+$/' )]
    #[TranslatablePropertyName( 'Big Integer' )]
    #[ORM\Column( type: 'bigint', nullable: true )]
    protected int|string|null $colBigint = null;

    #[TranslatablePropertyName( 'Boolean' )]
    #[ORM\Column( type: 'boolean', nullable: true )]
    protected ?bool $colBoolean = null;

    /** @var string|null Returned as string to preserve decimal precision */
    #[Assert\Regex( pattern: '/^-?\d+(\.\d{1,4})?$/' )]
    #[TranslatablePropertyName( 'Decimal' )]
    #[ORM\Column( type: 'decimal', precision: 15, scale: 4, nullable: true )]
    protected ?string $colDecimal = null;

    #[Assert\Type( type: 'float' )]
    #[TranslatablePropertyName( 'Float' )]
    #[ORM\Column( type: 'float', nullable: true )]
    protected ?float $colFloat = null;

    /** PostgreSQL-only: TIMESTAMP WITH TIME ZONE */
    #[Assert\Type( type: DateTimeInterface::class )]
    #[TranslatablePropertyName( 'Datetime with Timezone' )]
    #[ORM\Column( type: 'datetimetz', nullable: true )]
    protected ?DateTimeInterface $colDatetimetz = null;

    #[Assert\Type( type: DateTimeInterface::class )]
    #[TranslatablePropertyName( 'Date' )]
    #[ORM\Column( type: 'date', nullable: true )]
    protected ?DateTimeInterface $colDate = null;

    #[Assert\Type( type: DateTimeInterface::class )]
    #[TranslatablePropertyName( 'Time' )]
    #[ORM\Column( type: 'time', nullable: true )]
    protected ?DateTimeInterface $colTime = null;

    #[Assert\Type( type: 'array' )]
    #[TranslatablePropertyName( 'JSON' )]
    #[ORM\Column( type: 'json', nullable: true )]
    protected ?array $colJson = null;

    /** PostgreSQL: BYTEA */
    #[TranslatablePropertyName( 'Blob' )]
    #[ORM\Column( type: 'blob', nullable: true )]
    protected mixed $colBlob = null;

    /** PostgreSQL: UUID (native) */
    #[Assert\Uuid]
    #[TranslatablePropertyName( 'GUID' )]
    #[ORM\Column( type: 'guid', nullable: true )]
    protected ?string $colGuid = null;

    /** PHP-serialized TEXT */
    #[Assert\Type( type: 'array' )]
    #[TranslatablePropertyName( 'Array' )]
    #[ORM\Column( type: 'json', nullable: true )]
    protected ?array $colArray = null;

    /** Comma-separated TEXT */
    #[Assert\Type( type: 'array' )]
    #[TranslatablePropertyName( 'Simple Array' )]
    #[ORM\Column( type: 'simple_array', nullable: true )]
    protected ?array $colSimpleArray = null;

    /** PostgreSQL: BYTEA (same underlying type as blob) */
    #[TranslatablePropertyName( 'Binary' )]
    #[ORM\Column( type: 'binary', nullable: true )]
    protected mixed $colBinary = null;


    public function __construct()
    {
        $this->createdAt = new DateTime();
    }


    public function getColText(): ?string { return $this->colText; }

    public function setColText( ?string $v ): self { $this->colText = $v; return $this; }

    public function getColInteger(): ?int { return $this->colInteger; }

    public function setColInteger( ?int $v ): self { $this->colInteger = $v; return $this; }

    public function getColSmallint(): ?int { return $this->colSmallint; }

    public function setColSmallint( ?int $v ): self { $this->colSmallint = $v; return $this; }

    public function getColBigint(): int|string|null { return $this->colBigint; }

    public function setColBigint( int|string|null $v ): self { $this->colBigint = $v; return $this; }

    public function getColBoolean(): ?bool { return $this->colBoolean; }

    public function setColBoolean( ?bool $v ): self { $this->colBoolean = $v; return $this; }

    public function getColDecimal(): ?string { return $this->colDecimal; }

    public function setColDecimal( ?string $v ): self { $this->colDecimal = $v; return $this; }

    public function getColFloat(): ?float { return $this->colFloat; }

    public function setColFloat( ?float $v ): self { $this->colFloat = $v; return $this; }

    public function getColDatetimetz(): ?DateTimeInterface { return $this->colDatetimetz; }

    public function setColDatetimetz( ?DateTimeInterface $v ): self { $this->colDatetimetz = $v; return $this; }

    public function getColDate(): ?DateTimeInterface { return $this->colDate; }

    public function setColDate( ?DateTimeInterface $v ): self { $this->colDate = $v; return $this; }

    public function getColTime(): ?DateTimeInterface { return $this->colTime; }

    public function setColTime( ?DateTimeInterface $v ): self { $this->colTime = $v; return $this; }

    public function getColJson(): ?array { return $this->colJson; }

    public function setColJson( ?array $v ): self { $this->colJson = $v; return $this; }

    public function getColBlob(): mixed { return $this->colBlob; }

    public function setColBlob( mixed $v ): self { $this->colBlob = $v; return $this; }

    public function getColGuid(): ?string { return $this->colGuid; }

    public function setColGuid( ?string $v ): self { $this->colGuid = $v; return $this; }

    public function getColArray(): ?array { return $this->colArray; }

    public function setColArray( ?array $v ): self { $this->colArray = $v; return $this; }

    public function getColSimpleArray(): ?array { return $this->colSimpleArray; }

    public function setColSimpleArray( ?array $v ): self { $this->colSimpleArray = $v; return $this; }

    public function getColBinary(): mixed { return $this->colBinary; }

    public function setColBinary( mixed $v ): self { $this->colBinary = $v; return $this; }

    public function setEmail( ?string $v ): self { $this->email = $v; return $this; }

    public function getEmail(): ?string { return $this->email; }

    public function setPassword( ?string $password ): self
    {
        if ( $password !== null )
            $this->password = password_hash( $password, PASSWORD_BCRYPT );

        return $this;
    }

    public function getPassword(): ?string { return $this->password; }

}
