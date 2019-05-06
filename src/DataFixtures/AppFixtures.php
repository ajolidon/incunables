<?php

namespace App\DataFixtures;

use App\Entity\Language;
use App\Entity\RelationType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $data = file_get_contents('https://www.loc.gov/marc/languages/language_code.html');

        $re = '/<td class="code">.*([a-z]{3})<\/td>\n\s*<td>(.+)<\/td>/m';

        preg_match_all($re, $data, $matches, PREG_SET_ORDER, 0);

        foreach($matches as $match){
            $language = new Language();
            $language->setAbbreviation($match[1]);
            $language->setName($match[2]);
            $manager->persist($language);
        }

        $manager->flush();

        $data = file_get_contents('https://www.loc.gov/marc/relators/relacode.html');

        preg_match_all($re, $data, $matches, PREG_SET_ORDER, 0);

        foreach($matches as $match){
            $relationType = new RelationType();
            $relationType->setAbbreviation($match[1]);
            $relationType->setName($match[2]);
            $manager->persist($relationType);
        }

        $manager->flush();
    }
}
