<?php
declare(strict_types=1);

namespace Felds\TusServerBundle\Controller;

use Felds\TusServerBundle\Entity\AbstractUpload;
use Felds\TusServerBundle\UploadManager;
use Felds\TusServerBundle\Util\MetadataParser;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @TODO Config max size (maybe it should be in the entity?)
 *
 * @Route("/")
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

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(UploadManager $manager, RouterInterface $router)
    {
        $this->manager = $manager;
        $this->router = $router;
    }

    /**
     * @Route("/", methods={"OPTIONS"})
     */
    public function optionsAction()
    {
        return new Response(
            '', Response::HTTP_NO_CONTENT, [
                'Tus-Resumable' => self::TUS_VERSION,
                'Tus-Version' => self::TUS_VERSION,
                'Tus-Max-Size' => self::MAX_SIZE,
                'Tus-Extension' => implode(',', self::EXTENSIONS),
            ]
        );
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
        $meta = MetadataParser::parse($request->headers->get('Upload-Metadata'));

        $totalBytes = ($request->headers->get('Upload-Defer-Length') === "1")
            ? null
            : (int)$request->headers->get('Upload-Length');

        if ($totalBytes === 0) {
            return new Response("Invalid upload length: {$totalBytes} bytes.", Response::HTTP_BAD_REQUEST);
        }

        if ($totalBytes > self::MAX_SIZE) {
            return new Response(
                "The maximum entity size " . self::MAX_SIZE . " is bytes.",
                Response::HTTP_REQUEST_ENTITY_TOO_LARGE
            );
        } else {
            $entity = $this->manager->createUpload();
            $entity->setOriginalFilename(@$meta['name']);
            $entity->setMimeType(@$meta['type']);
            $entity->setTotalBytes($totalBytes);

            // Create the file
            // Btw, is this the right place to create the file?
            // @TODO return an actual response in case of failure
            if (!touch($entity->getPath())) {
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
     * @Route("/{id}", methods={"PATCH"})
     * @param AbstractUpload $entity
     */
    public function patchAction(AbstractUpload $entity)
    {
        dump($entity);
        die;
    }
}
