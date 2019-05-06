<?php

namespace App\Helper;


interface EquatableInterface extends HashableInterface
{
    public function equals(EquatableInterface $other): bool;
}