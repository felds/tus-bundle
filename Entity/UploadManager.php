<?php
declare(strict_types=1);

namespace Felds\TusServerBundle\Entity;

use Doctrine\ORM\EntityManagerInterface;
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
        $this->maxSize = $maxSize;
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

    public function createUpload(): AbstractUpload
    {
        $path = tempnam(sys_get_temp_dir(), 'tus/');

        return new $this->class($path);
    }

    public function save(AbstractUpload $entity, bool $andFlush = true): void
    {
        $this->em->persist($entity);

        if ($andFlush) {
            $this->em->flush();
        }
    }
}
