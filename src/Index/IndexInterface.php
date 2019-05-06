<?php

namespace App\Index;

interface IndexInterface
{
    public function getEntries(): array;
    public function update(): string;
}