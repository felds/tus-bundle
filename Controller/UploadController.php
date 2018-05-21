<?php
declare(strict_types=1);

namespace Felds\TusServerBundle\Controller;

use Felds\TusServerBundle\Model\UploadManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @todo config max size
 */
class UploadController
{
    const TUS_VERSION = '1.0.0';
    const EXTENSIONS = ['creation', 'expiration', 'termination'];
    const MAX_SIZE = 3221225472;

    /**
     * @var UploadManager
     */
    private $manager;

    public function __construct(UploadManager $manager)
    {
        $this->manager = $manager;
    }

    public function optionsAction()
    {
        return new Response('', Response::HTTP_NO_CONTENT, [
            'Tus-Resumable' => self::TUS_VERSION,
            'Tus-Version' => self::TUS_VERSION,
            'Tus-Max-Size' => self::MAX_SIZE,
            'Tus-Extension' => implode(',', self::EXTENSIONS),
        ]);
    }

    /**
     * Create an upload and return the patch url.
     *
     * @Route("/", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request)
    {
        $path = $this->tusDir . '/' . uniqid();
        $meta = TusUtils::parseMetadata($request);
        $filename = $meta['name'];
        $totalBytes = ($request->headers->get('Upload-Defer-Length') === 1)
            ? null
            : (int)$request->headers->get('Upload-Length');

        if ($totalBytes === 0) {
            throw new RuntimeException("Invalid upload length: {$totalBytes}");
        }

        if ($totalBytes > self::MAX_SIZE) {
            return new Response('', Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
        } else {
            $entity = new Upload($path, $filename, $totalBytes);

            // Create the file
            if (!touch($entity->getPath())) {
                throw new RuntimeException("Unable to create file: {$entity->getPath()}");
            }

            $this->em->persist($entity);
            $this->em->flush();

            return new Response('', Response::HTTP_CREATED, [
                'Tus-Resumable' => self::TUS_VERSION,
                'Location' => $this->generateUrl('app_upload_send', ['id' => $entity->getId()]),
                'Upload-Expires' => $entity->getExpiresAt()->format(DATE_RFC7231),
            ]);
        }
    }
}
