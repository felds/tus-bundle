<?php
declare(strict_types=1);

namespace Felds\TusServerBundle\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Felds\SizeStrToBytes\SizeStrToBytes;
use InvalidArgumentException;

final class UploadManager
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string|null
     */
    private $expiresIn;

    /**
     * @var string|null
     */
    private $maxSize;

    public function __construct(
        string $class,
        ?string $expiresIn,
        ?string $maxSize,
        EntityManagerInterface $em
    ) {
        $this->checkClass($class);

        $this->class = $class;
        $this->expiresIn = $expiresIn;
        $this->maxSize = $maxSize ? SizeStrToBytes::convert($maxSize) : null;
        $this->em = $em;
    }

    /**
     * @throws InvalidArgumentException
     */
    private static function checkClass(string $class): void
    {
        $correctClass = AbstractUpload::class;

        if ( ! is_subclass_of($class, $correctClass)) {
            throw new InvalidArgumentException("{$class} must extend {$correctClass}");
        }
    }

    /**
     * @throws \Exception When failed to create an expiration time.
     */
    public function createUpload(): AbstractUpload
    {
        $path = tempnam(sys_get_temp_dir(), 'tus/');
        $expiresAt = new \DateTimeImmutable($this->expiresIn);

        return new $this->class($path, $expiresAt);
    }

    public function save(AbstractUpload $entity, bool $andFlush = true): void
    {
        $this->em->persist($entity);

        if ($andFlush) {
            $this->em->flush();
        }
    }

    public function findUpload($id): AbstractUpload
    {
        return $this->em->find($this->class, $id);
    }

    /**
     * @TODO remove file from disk
     */
    public function remove(AbstractUpload $entity): void
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function getMaxSize(): ?int
    {
        return $this->maxSize;
    }
}
