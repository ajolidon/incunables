<?php

namespace App\Index\Indizes;


use App\Entity\IndexEntry;
use App\Index\AbstractIndex;

class TitleIndex extends AbstractIndex
{
    protected function getType(): int
    {
        return IndexEntry::TYPE_TITLE;
    }

    public function update(): string
    {
        // TODO: Implement update() method.
    }

}