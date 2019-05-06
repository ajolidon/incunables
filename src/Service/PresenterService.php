<?php

namespace App\Service;


use App\Entity\SearchEntry;

class PresenterService
{
    public function getSearchMatch(string $query, SearchEntry $searchEntry)
    {
        $value = htmlentities($searchEntry->getValue());
        $highlighted = '<div style="display:inline;font-weight:bold;background-color:yellow">' . $query . '</div>';

        return str_ireplace($query, $highlighted, $value);
    }
}