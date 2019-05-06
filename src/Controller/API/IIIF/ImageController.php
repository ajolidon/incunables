<?php

namespace App\Controller\API\IIIF;

use App\Entity\Scan;
use App\Service\IIIF\ImageService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/api/iiif/image")
 */
class ImageController extends AbstractIIIFController
{
    /**
     * @Route("/profile.json", name="api_iiif_image_profile")
     */
    public function profile(){
        return $this->json(array_merge([
            '@context' => 'http://iiif.io/api/image/2/context.json',
            '@id' => $this->generateUrl('api_iiif_image_profile', [],UrlGeneratorInterface::ABSOLUTE_URL),
            '@type' => 'iiif:ImageProfile',
        ], $this->getProfileFeatures(false)));
    }

    /**
     * @Route("/{id}", name="api_iiif_image_base")
     */
    public function base($id){
        return $this->redirectToRoute('api_iiif_image_info', ['id' => $id]);
    }
    
    /**
     * @Route("/{id}/info.json", name="api_iiif_image_info")
     */
    public function info(Scan $scan){
        return $this->json([
            '@context' => 'http://iiif.io/api/image/2/context.json',
            '@id' => $this->generateUrl('api_iiif_image_base', ['id' => $scan->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            '@type' => 'iiif:Image',
            'protocol' => 'http://iiif.io/api/image',
            'width' => $scan->getWidth(),
            'height' => $scan->getHeight(),
            'profile' => array_merge($this->getProfileFeatures()),
        ]);
    }

    /**
     * @Route("/{id}/{region}/{size}/{rotation}/{quality}.{format}", name="api_iiif_image")
     * @throws \Exception
     * @see ImageService
     */
    public function image(ImageService $imageService, Scan $scan, $region, $size, $rotation, $quality, $format){
        $image = $imageService->createImage($scan, $region, $size, $rotation, $quality, $format);
        return new Response($image, 200, ['Content-Type' => 'image/jpeg', 'Content-Disposition' => 'inline']);
    }
}