<?php

namespace App\Controller;

use App\Entity\Incunable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/uv")
 */
class UniversalViewerController extends AbstractController
{
    /**
     * @Route("/{id}/{index}/", name="universal_viewer")
     */
    public function universalViewer(Incunable $incunable, $index)
    {
        $manifest = $this->generateUrl('api_iiif_presentation_manifest', ['id' => $incunable->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return $this->redirect('http://universalviewer.io/uv.html?manifest=' . urlencode($manifest) . '#?cv=' . $index);
    }
}
