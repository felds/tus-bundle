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
        if (!$class instanceof AbstractUpload) {
            $correctClass = AbstractUpload::class;
            throw new InvalidArgumentException("{$class} must extend {$correctClass}");
        }

        $this->class = $class;
    }
}
