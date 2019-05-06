<?php

namespace App\Helper\Search;

use App\Entity\Incunable;
use App\Entity\SearchEntry;

class SearchResult
{
    /**
     * @var Incunable
     */
    private $incunable;

    /**
     * @var int
     */
    private $minimumPriority;

    /**
     * @var SearchEntry[]
     */
    private $searchEntries = [];

    /**
     * @param SearchEntry $searchEntry
     * @return SearchResult
     * @throws \Exception
     */
    public function addMatch(SearchEntry $searchEntry): self
    {
        if(!empty($this->incunable) && $this->incunable != $searchEntry->getIncunable()){
            throw new \Exception('Incunable in search entry mismatch.');
        }

        $this->incunable = $searchEntry->getIncunable();
        if(is_null($this->minimumPriority) || $this->minimumPriority > $searchEntry->getPriority()){
            $this->minimumPriority = $searchEntry->getPriority();
        }
        $this->searchEntries[] = $searchEntry;

        return $this;
    }

    /**
     * @return Incunable
     */
    public function getIncunable(): Incunable
    {
        return $this->incunable;
    }

    /**
     * @return SearchEntry[]
     */
    public function getMatches(): array
    {
        $this->sort();
        return $this->searchEntries;
    }

    /**
     * @return int
     */
    public function getPriorityMinimum(): int
    {
        return $this->minimumPriority;
    }

    protected function sort()
    {
        usort($this->searchEntries, function(SearchEntry $a, SearchEntry $b){
            if($a->getPriority() == $b->getPriority() && $a->getId() == $b->getId()){
                return 0;
            }

            if($a->getPriority() != $b->getPriority()){
                return $a->getPriority() < $b->getPriority() ? -1 : 1;
            }

            return $a->getId() < $b->getId() ? -1 : 1;
        });

        $usedIds = [];
        $hasPublication = false;

        foreach($this->searchEntries as $key => $searchEntry){
            if(in_array($searchEntry->getId(), $usedIds)){
                unset($this->searchEntries[$key]);
                continue;
            }

            if($searchEntry->getField() == 'Publikation'){
                if(!$hasPublication){
                    $hasPublication = true;
                    continue;
                }

                unset($this->searchEntries[$key]);
            }
        }

        $this->searchEntries = array_values($this->searchEntries);
    }
}