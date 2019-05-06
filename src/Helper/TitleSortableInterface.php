<?php

namespace App\Helper;


use App\Entity\Title;
use Doctrine\Common\Collections\Collection;

interface TitleSortableInterface
{
    /**
     * @param int|null $type
     * @return Collection|Title[]
     */
    public function getTitles(int $type = null): Collection;

    public function getPreferredTitle(): ?Title;
}