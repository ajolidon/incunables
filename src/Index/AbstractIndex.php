<?php

namespace App\Index;


use App\Entity\IndexEntry;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractIndex implements IndexInterface
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function present(): string
    {
        // TODO: Implement present() method.
    }


    /**
     * @return IndexEntry[]
     */
    public function getEntries(): array
    {
        /* @var \App\Repository\IndexEntryRepository $repo */
        $repo = $this->manager->getRepository(IndexEntry::class);
        return $repo->findBy(['parent' => null, 'type' => $this->getType()]);
    }

    abstract protected function getType(): int;

    //abstract protected function getPresenter();
}