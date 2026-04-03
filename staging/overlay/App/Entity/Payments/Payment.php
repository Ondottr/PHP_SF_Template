<?php declare( strict_types=1 );

namespace App\Entity\Payments;

use App\Repository\Payments\PaymentRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use PHP_SF\System\Attributes\Validator\TranslatablePropertyName;
use PHP_SF\System\Classes\Abstracts\AbstractEntity;
use PHP_SF\System\Traits\ModelProperty\ModelPropertyCreatedAtTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity( repositoryClass: PaymentRepository::class )]
#[ORM\Table( name: 'payment' )]
#[ORM\Cache( usage: 'READ_WRITE' )]
class Payment extends AbstractEntity
{
    use ModelPropertyCreatedAtTrait;


    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\Regex( pattern: '/^\d+(\.\d{1,2})?$/' )]
    #[TranslatablePropertyName( 'Amount' )]
    #[ORM\Column( type: 'decimal', precision: 10, scale: 2 )]
    protected ?string $amount = null;

    #[Assert\NotBlank]
    #[Assert\Length( exactly: 3 )]
    #[Assert\Regex( pattern: '/^[A-Z]{3}$/' )]
    #[TranslatablePropertyName( 'Currency' )]
    #[ORM\Column( type: 'string', length: 3 )]
    protected string $currency = 'USD';

    #[Assert\NotBlank]
    #[Assert\Choice( choices: [ 'pending', 'completed', 'failed', 'refunded' ] )]
    #[TranslatablePropertyName( 'Status' )]
    #[ORM\Column( type: 'string' )]
    protected string $status = 'pending';

    // --- MariaDB type coverage ---

    #[TranslatablePropertyName( 'Text' )]
    #[ORM\Column( type: 'text', nullable: true )]
    protected ?string $colText = null;

    #[Assert\Range( min: -2147483648, max: 2147483647 )]
    #[TranslatablePropertyName( 'Integer' )]
    #[ORM\Column( type: 'integer', nullable: true )]
    protected ?int $colInteger = null;

    #[Assert\Range( min: -32768, max: 32767 )]
    #[TranslatablePropertyName( 'Smallint' )]
    #[ORM\Column( type: 'smallint', nullable: true )]
    protected ?int $colSmallint = null;

    /** @var int|string|null Doctrine returns bigint as string to avoid overflow */
    #[Assert\Regex( pattern: '/^-?\d+$/' )]
    #[TranslatablePropertyName( 'Bigint' )]
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

    #[Assert\Type( type: DateTimeInterface::class )]
    #[TranslatablePropertyName( 'Date' )]
    #[ORM\Column( type: 'date', nullable: true )]
    protected ?DateTimeInterface $colDate = null;

    #[Assert\Type( type: DateTimeInterface::class )]
    #[TranslatablePropertyName( 'Time' )]
    #[ORM\Column( type: 'time', nullable: true )]
    protected ?DateTimeInterface $colTime = null;

    /**
     * MariaDB 10.2+: stored as LONGTEXT with a CHECK constraint ensuring valid JSON.
     * Unlike MySQL, MariaDB's JSON is an alias for LONGTEXT — not a native binary type.
     */
    #[Assert\Type( type: 'array' )]
    #[TranslatablePropertyName( 'JSON' )]
    #[ORM\Column( type: 'json', nullable: true )]
    protected ?array $colJson = null;

    /** MariaDB: LONGBLOB */
    #[TranslatablePropertyName( 'Blob' )]
    #[ORM\Column( type: 'blob', nullable: true )]
    protected mixed $colBlob = null;

    /** MariaDB: CHAR(36) */
    #[Assert\Uuid]
    #[TranslatablePropertyName( 'GUID' )]
    #[ORM\Column( type: 'guid', nullable: true )]
    protected ?string $colGuid = null;

    /** PHP-serialized LONGTEXT */
    #[Assert\Type( type: 'array' )]
    #[TranslatablePropertyName( 'Array' )]
    #[ORM\Column( type: 'json', nullable: true )]
    protected ?array $colArray = null;

    /** Comma-separated LONGTEXT */
    #[Assert\Type( type: 'array' )]
    #[TranslatablePropertyName( 'Simple Array' )]
    #[ORM\Column( type: 'simple_array', nullable: true )]
    protected ?array $colSimpleArray = null;

    /** MariaDB: VARBINARY */
    #[TranslatablePropertyName( 'Binary' )]
    #[ORM\Column( type: 'binary', length: 20, nullable: true )]
    protected mixed $colBinary = null;


    public function __construct()
    {
        $this->createdAt = new DateTime();
    }


    public function getAmount(): ?string { return $this->amount; }

    public function setAmount( ?string $v ): self { $this->amount = $v; return $this; }

    public function getCurrency(): string { return $this->currency; }

    public function setCurrency( string $v ): self { $this->currency = $v; return $this; }

    public function getStatus(): string { return $this->status; }

    public function setStatus( string $v ): self { $this->status = $v; return $this; }

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

}
