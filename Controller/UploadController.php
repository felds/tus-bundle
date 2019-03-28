<?php
declare(strict_types=1);

namespace Felds\TusServerBundle\Controller;

use Felds\TusServerBundle\Entity\UploadManager;
use Felds\TusServerBundle\Util\MetadataParser;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @TODO Config max size (maybe it should be in the entity?)
 */
class UploadController
{
    const TUS_VERSION = '1.0.0';
    const EXTENSIONS = ['creation', 'expiration'];

    /**
     * @var UploadManager
     */
    private $manager;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(UploadManager $manager, RouterInterface $router)
    {
        $this->manager = $manager;
        $this->router = $router;
    }

    public function optionsAction()
    {
        $headers = [
            'Tus-Resumable' => self::TUS_VERSION,
            'Tus-Version' => self::TUS_VERSION,
            'Tus-Extension' => implode(',', self::EXTENSIONS),
        ];

        if ($this->manager->getMaxSize() !== null) {
            $headers['Tus-Max-Size'] = $this->manager->getMaxSize();
        }

        return new Response('', Response::HTTP_NO_CONTENT, $headers);
    }

    /**
     * Create an upload and return the patch url.
     */
    public function createAction(Request $request)
    {
        $rawMeta = $request->headers->get('Upload-Metadata');
        $meta = MetadataParser::parse($rawMeta ?? '');

        // @TODO validate required metadata

        $totalBytes = ($request->headers->get('Upload-Defer-Length') === 1)
            ? null
            : (int)$request->headers->get('Upload-Length');

        if ($totalBytes === 0) {
            return new Response("Invalid upload length: {$totalBytes} bytes.", Response::HTTP_BAD_REQUEST);
        }

        $maxSize = $this->manager->getMaxSize();
        if (false && $maxSize && $totalBytes > $maxSize) {
            return new Response(
                "The maximum entity size is {$maxSize} bytes.",
                Response::HTTP_REQUEST_ENTITY_TOO_LARGE
            );
        } else {
            $entity = $this->manager->createUpload();
            $entity->setOriginalFilename($meta['name'] ?? null);
            $entity->setMimeType($meta['type'] ?? null);
            $entity->setTotalBytes($totalBytes);

            // Create the file
            // Btw, is this the right place to create the file?
            // @TODO return an actual response in case of failure
            if ( ! touch($entity->getPath())) {
                throw new RuntimeException("Unable to create file: {$entity->getPath()}");
            }

            $this->manager->save($entity);

            $response = new Response(
                '', Response::HTTP_CREATED, [
                    'Tus-Resumable' => self::TUS_VERSION,
                    'Location' => $this->router->generate('tus_upload_patch', ['id' => $entity->getId()]),
                ]
            );

            if ($entity->getExpiresAt()) {
                $response->headers->set('Upload-Expires', $entity->getExpiresAt()->format(DATE_RFC7231));
            }

            return $response;
        }
    }

    /**
     * Send content to be appended to the upload.
     *
     * @param Request $request
     * @param mixed $id
     * @return Response
     *
     * @TODO cut off the upload when it exceeds the declared length
     */
    public function patchAction(Request $request, $id)
    {
        $entity = $this->manager->findUpload($id);

        $f = fopen($entity->getPath(), 'a');
        stream_copy_to_stream($request->getContent(true), $f);
        fclose($f);

        $entity->setUploadedBytes(filesize($entity->getPath()));

        $this->manager->save($entity);

        $headers = [
            'Tus-Resumable' => self::TUS_VERSION,
            'Upload-Offset' => $entity->getUploadedBytes(),
        ];
        if ($entity->getExpiresAt()) {
            $headers['Upload-Expires'] = $entity->getExpiresAt()->format(DATE_RFC7231);
        }

        return new Response('', Response::HTTP_NO_CONTENT, $headers);
    }

    /**
     * Check the status of the upload.
     *
     * @param Request $request
     * @param mixed $id
     * @return Response
     */
    public function sendHeadAction(Request $request, $id)
    {
        $entity = $this->manager->findUpload($id);

        $headers = [
            'Tus-Resumable' => self::TUS_VERSION,
            'Cache-Control' => 'no-store',
            'Upload-Offset' => $entity->getUploadedBytes(),
        ];

        if ($entity->getTotalBytes() === null) {
            $headers['Upload-Defer-Length'] = 1;
        } else {
            $headers['Upload-Length'] = $entity->getTotalBytes();
        }

        return new Response('', Response::HTTP_OK, $headers);
    }

    /**
     * Cancel the download and cleanup resources.
     *
     * @param Request $request
     * @param mixed $id
     * @return Response
     */
    public function deleteAction(Request $request, $id)
    {
        $entity = $this->manager->findUpload($id);
        $this->manager->remove($entity);

        return new Response('', Response::HTTP_NO_CONTENT, [
            'Tus-Resumable' => self::TUS_VERSION,
        ]);
    }
}
