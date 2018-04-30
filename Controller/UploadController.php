<?php
declare(strict_types=1);

namespace Felds\TusServerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * @todo config max size
 */
class UploadController
{
    const TUS_VERSION = '1.0.0';
    const EXTENSIONS = ['creation', 'expiration', 'termination'];
    const MAX_SIZE = 3221225472; // (1024 ** 3) * 3 == 3GB

    public function optionsAction()
    {
        return new Response('', Response::HTTP_NO_CONTENT, [
            'Tus-Resumable' => self::TUS_VERSION,
            'Tus-Version' => self::TUS_VERSION,
            'Tus-Max-Size' => self::MAX_SIZE,
            'Tus-Extension' => implode(',', self::EXTENSIONS),
        ]);
    }
}
