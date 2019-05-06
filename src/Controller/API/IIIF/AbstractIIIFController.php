<?php

namespace App\Controller\API\IIIF;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AbstractIIIFController extends AbstractController
{
    protected function getProfileFeatures($includeLevel = true): array
    {
        if(!$includeLevel) {
            return ['formats' => ['jpg'],
                'qualities' => ['color', 'default'],
                'features' => [
                    'baseUriRedirect',
                    'mirroring',
                    'regionByPct',
                    'regionByPx',
                    'regionSquare',
                    'rotationArbitrary',
                    'sizeByConfinedWh',
                    'sizeByDistortedWh',
                    'sizeByH',
                    'sizeByPct',
                    'sizeByW',
                    'sizeByWh',
                ]];
        }

        return ['http://iiif.io/api/image/2/level2.json', ['formats' => ['jpg'],
            'qualities' => ['color', 'default'],
            'features' => [
                'baseUriRedirect',
                'mirroring',
                'regionByPct',
                'regionByPx',
                'regionSquare',
                'rotationArbitrary',
                'sizeByConfinedWh',
                'sizeByDistortedWh',
                'sizeByH',
                'sizeByPct',
                'sizeByW',
                'sizeByWh',
            ]]];
    }
}