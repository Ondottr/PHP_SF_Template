<?php declare( strict_types=1 );

namespace App\Entity\Blog;

use App\Repository\Blog\PostRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use PHP_SF\System\Attributes\Validator\TranslatablePropertyName;
use PHP_SF\System\Classes\Abstracts\AbstractEntity;
use PHP_SF\System\Traits\ModelProperty\ModelPropertyCreatedAtTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity( repositoryClass: PostRepository::class )]
#[ORM\Table( name: 'post' )]
#[ORM\Cache( usage: 'READ_WRITE' )]
class Post extends AbstractEntity
{
    use ModelPropertyCreatedAtTrait;


    #[Assert\NotBlank]
    #[Assert\Length( min: 1, max: 255 )]
    #[TranslatablePropertyName( 'Title' )]
    #[ORM\Column( type: 'string', unique: true )]
    protected ?string $title = null;

    #[TranslatablePropertyName( 'Content' )]
    #[ORM\Column( type: 'text', nullable: true )]
    protected ?string $content = null;

    #[Assert\NotBlank]
    #[Assert\Choice( choices: [ 'draft', 'published', 'archived' ] )]
    #[TranslatablePropertyName( 'Status' )]
    #[ORM\Column( type: 'string' )]
    protected string $status = 'draft';

    // --- MySQL type coverage ---

    #[Assert\Range( min: -2147483648, max: 2147483647 )]
    #[TranslatablePropertyName( 'Integer' )]
    #[ORM\Column( type: 'integer', nullable: true )]
    protected ?int $colInteger = null;

    #[Assert\Range( min: -32768, max: 32767 )]
    #[TranslatablePropertyName( 'Smallint' )]
    #[ORM\Column( type: 'smallint', nullable: true )]
    protected ?int $colSmallint = null;

    /** @var int|string|null Doctrine returns bigint as string to avoid overflow */
    #[Assert\Type( type: 'numeric' )]
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

    /** MySQL 5.7.8+: native JSON column */
    #[Assert\Type( type: 'array' )]
    #[TranslatablePropertyName( 'JSON' )]
    #[ORM\Column( type: 'json', nullable: true )]
    protected ?array $colJson = null;

    /** MySQL: LONGBLOB */
    #[TranslatablePropertyName( 'Blob' )]
    #[ORM\Column( type: 'blob', nullable: true )]
    protected mixed $colBlob = null;

    /** MySQL: CHAR(36) */
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

    /** MySQL: VARBINARY */
    #[TranslatablePropertyName( 'Binary' )]
    #[ORM\Column( type: 'binary', length: 20, nullable: true )]
    protected mixed $colBinary = null;


    public function __construct()
    {
        $this->createdAt = new DateTime();
    }


    public function getTitle(): ?string { return $this->title; }

    public function setTitle( ?string $v ): self { $this->title = $v; return $this; }

    public function getContent(): ?string { return $this->content; }

    public function setContent( ?string $v ): self { $this->content = $v; return $this; }

    public function getStatus(): string { return $this->status; }

    public function setStatus( string $v ): self { $this->status = $v; return $this; }

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
