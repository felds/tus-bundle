<?php
declare(strict_types=1);

namespace Felds\TusServerBundle\Controller;

use Felds\TusServerBundle\UploadManager;
use Felds\TusServerBundle\Util\MetadataParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

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

    public function options(Request $request)
    {
        $response = new Response('', Response::HTTP_NO_CONTENT);

        $response->headers->set('Tus-Resumable', self::TUS_VERSION);
        $response->headers->set('Tus-Version', self::TUS_VERSION);
        $response->headers->set('Tus-Extension', implode(',', self::EXTENSIONS));

        $response->prepare($request);

        return $response;
    }

    /**
     * Create an upload and return the patch url.
     *
     * @TODO validate required metadata
     */
    public function create(Request $request)
    {
        $response = new Response('', Response::HTTP_CREATED);

        $rawMeta = $request->headers->get('Upload-Metadata');
        $meta = MetadataParser::parse($rawMeta ?? '');

        $totalBytes = ($request->headers->get('Upload-Defer-Length') === 1)
            ? null
            : (int)$request->headers->get('Upload-Length');
        $maxSize = $this->manager->getMaxSize();
        if ($totalBytes && $totalBytes > $maxSize) {
            $response->setContent("The maximum entity size is {$maxSize} bytes.");
            $response->setStatusCode(Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
            goto done;
        }

        $entity = $this->manager->createUpload();
        $entity->setOriginalFilename($meta['name'] ?? null);
        $entity->setMimeType($meta['type'] ?? null);
        $entity->setTotalBytes($totalBytes);
        $this->manager->save($entity);

        $response->headers->set('Tus-Resumable', self::TUS_VERSION);
        $response->headers->set('Location', $this->router->generate('tus_upload_patch', ['id' => $entity->getId()]));

        if ($entity->getExpiresAt()) {
            $response->headers->set('Upload-Expires', $entity->getExpiresAt()->format(DATE_RFC7231));
        }

        done:
        $response->prepare($request);

        return $response;
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
