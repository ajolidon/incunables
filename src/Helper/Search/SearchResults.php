<?php

namespace App\Helper\Search;


use App\Entity\SearchEntry;
use App\Repository\SearchEntryRepository;

class SearchResults
{
    /**
     * @var SearchResult[]
     */
    private $searchResults = [];

    /**
     * SearchResults constructor.
     * @param SearchEntry[] $searchEntries
     */
    public function __construct(array $searchEntries)
    {
        foreach($searchEntries as $searchEntry){
            $this->addResult($searchEntry);
        }
    }

    /**
     * @return SearchResult[]
     */
    public function getResults(){
        $this->sort();
        return $this->searchResults;
    }

    protected function addResult(SearchEntry $searchEntry): self
    {
        foreach($this->searchResults as $searchResult){
            if($searchResult->getIncunable() == $searchEntry->getIncunable())
            {
                $searchResult->addMatch($searchEntry);
                return $this;
            }
        }

        $searchResult = new SearchResult();
        $searchResult->addMatch($searchEntry);

        $this->searchResults[] = $searchResult;

        return $this;
    }

    protected function sort()
    {
        usort($this->searchResults, function(SearchResult $a, SearchResult $b){
            if($a->getPriorityMinimum() == $b->getPriorityMinimum()){
                return 0;
            }

            return $a->getPriorityMinimum() < $b->getPriorityMinimum() ? -1 : 1;
        });
    }
}