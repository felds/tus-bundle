<?php
declare(strict_types=1);

namespace Felds\TusServerBundle\Model;

use InvalidArgumentException;

final class UploadManager
{
    /**
     * @var string
     */
    private $class;

    public function __construct(string $class, $expiration = null)
    {
        $this->checkClass($class);

        $this->class = $class;
    }

    /**
     * @throws InvalidArgumentException
     */
    private static function checkClass(string $class): void
    {
        $correctClass = AbstractUpload::class;
        if (!is_subclass_of($class, $correctClass)) {
            throw new InvalidArgumentException("{$class} must extend {$correctClass}");
        }
    }
}
