<?php

namespace App\Controller\API\IIIF;

use App\Entity\Incunable;
use App\Entity\Scan;
use App\Service\IIIF\ImageService;
use App\Service\IncunableService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/api/iiif/presentation")
 */
class PresentationController extends AbstractIIIFController
{

    /**
     * @Route("/{id}", name="api_iiif_presentation_base")
     */
    public function base($id){
        return $this->redirectToRoute('api_iiif_presentation_manifest', ['id' => $id]);
    }

    /**
     * @Route("/{id}/manifest.json", name="api_iiif_presentation_manifest")
     */
    public function manifest(Incunable $incunable, IncunableService $incunableService){
        if($incunable->getScans()->isEmpty()){
            throw new NotFoundHttpException('This incunable does not have any scans.');
        }

        $metadata = [];

        $author = $incunableService->getAuthor($incunable);
        if(!empty($author)) {
            $metadata[] = ['label' => 'Author', 'value' => $author->__toString()];
        }

        return $this->json([
            '@context' => 'http://iiif.io/api/presentation/2/context.json',
            '@id' => $this->generateUrl('api_iiif_presentation_base', ['id' => $incunable->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            '@type' => 'sc:Manifest',
            'label' => $incunable->getPreferredTitle()->__toString(),
            'metadata' => $metadata,
            'sequences' => [
                $this->getSequence($incunable),
            ],
        ]);
    }

    /**
     * @Route("/sequence/{id}", name="api_iiif_presentation_sequence")
     */
    public function sequence(Incunable $incunable)
    {
        $sequence = ['@context' => 'http://iiif.io/api/presentation/2/context.json'];

        return $this->json(array_merge($sequence, $this->getSequence($incunable)));
    }

    protected function getSequence(Incunable $incunable)
    {
        $canvases = [];

        foreach($incunable->getScans() as $scan){
            $canvases[] = $this->getCanvas($scan);
        }

        return [
            '@id' => $this->generateUrl('api_iiif_presentation_sequence', ['id' => $incunable->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            '@type' => 'sc:Sequence',
            'startCanvas' => $this->generateUrl('api_iiif_presentation_canvas', ['id' => $incunable->getScans()->first()->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'canvases' => $canvases,
        ];
    }

    /**
     * @Route("/canvas/{id}", name="api_iiif_presentation_canvas")
     */
    public function canvas(Scan $scan)
    {
        $canvas = ['@context' => 'http://iiif.io/api/presentation/2/context.json'];

        return $this->json(array_merge($canvas, $this->getCanvas($scan)));
    }

    protected function getCanvas(Scan $scan): array
    {
        $metadata = [];
        $description = $scan->getDescription();
        if(!empty($description)){
            $metadata[] = ['label' => 'Description', 'value' => $description];
        }

        return [
            '@type' => 'sc:Canvas',
            '@id' => $this->generateUrl('api_iiif_presentation_canvas', ['id' => $scan->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'label' => $scan->getPageOfPages(),
            'width' => $scan->getWidth(),
            'height' => $scan->getHeight(),
            'metadata' => $metadata,
            'images' => [
                [
                    '@type' => 'oa:Annotation',
                    'motivation' => 'sc:painting',
                    'resource' => [
                        '@id' => $this->generateUrl('api_iiif_image', ['id' => $scan->getId(), 'region' => 'full', 'size' => 'full', 'rotation' => 0, 'quality' => 'default', 'format' => 'jpg'], UrlGeneratorInterface::ABSOLUTE_URL),
                        '@type' => 'dctypes:Image',
                        'format' => 'image/jpg',
                        'width' => $scan->getWidth(),
                        'height' => $scan->getHeight(),
                        'service' => [
                            "@context" =>  "http://iiif.io/api/image/2/context.json",
                            "@id" => $this->generateUrl('api_iiif_image_base', ['id' => $scan->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                            'protocol' => 'http://iiif.io/api/image',
                            'width' => $scan->getWidth(),
                            'height' => $scan->getHeight(),
                            "profile" => $this->getProfileFeatures(),
                        ],
                    ],
                    'on' => $this->generateUrl('api_iiif_presentation_canvas', ['id' => $scan->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                ],
            ],
        ];
    }
}