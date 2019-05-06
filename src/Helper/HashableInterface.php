<?php

namespace App\Helper;


interface HashableInterface
{
    public function updateHash(): string;
}