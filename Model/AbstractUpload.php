<?php
declare(strict_types=1);

namespace Felds\TusServerBundle\Model;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use RuntimeException;

abstract class AbstractUpload
{
    const EXPIRATION = '+1 day';

    /**
     * @var string
     * @ORM\Column()
     */
    private $path;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $totalBytes;

    /**
     * @var string
     * @ORM\Column(nullable=true)
     */
    private $originalFilename;

    /**
     * @var string
     * @ORM\Column(nullable=true)
     */
    private $mimeType;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $uploadedBytes;

    /**
     * @var DateTimeInterface
     * @ORM\Column(type="datetime_immutable")
     */
    private $expiresAt;

    /**
     * @param string $path The path to the file that will hold the upload.
     * @throws Exception
     */
    public function __construct(string $path)
    {
        $this->path = $path;
        $this->uploadedBytes = 0;
        $this->expiresAt = new DateTimeImmutable(static::EXPIRATION);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return int|null
     */
    public function getTotalBytes(): ?int
    {
        return $this->totalBytes;
    }

    /**
     * @param int|null $totalBytes
     */
    public function setTotalBytes(?int $totalBytes): void
    {
        if ($this->totalBytes) {
            throw new RuntimeException("Total bytes was already set.");
        }

        $this->totalBytes = $totalBytes;
    }

    /**
     * @return string
     */
    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    /**
     * @param string $originalFilename
     */
    public function setOriginalFilename(string $originalFilename): void
    {
        $this->originalFilename = $originalFilename;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     */
    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @return int
     */
    public function getUploadedBytes(): int
    {
        return $this->uploadedBytes;
    }

    /**
     * @param int $uploadedBytes
     */
    public function setUploadedBytes(int $uploadedBytes): void
    {
        $this->uploadedBytes = $uploadedBytes;
    }

    /**
     * @return DateTimeInterface
     */
    public function getExpiresAt(): DateTimeInterface
    {
        return $this->expiresAt;
    }

    /**
     * @param DateTimeInterface $expiresAt
     */
    public function setExpiresAt(DateTimeInterface $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }
}
