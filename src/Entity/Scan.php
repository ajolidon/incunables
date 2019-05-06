<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ScanRepository")
 */
class Scan
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $modified;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Incunable", inversedBy="scans")
     * @ORM\JoinColumn(nullable=false)
     */
    private $incunable;

    /**
     * @ORM\Column(type="integer")
     */
    private $width;

    /**
     * @ORM\Column(type="integer")
     */
    private $height;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPageOfPages(): ?string
    {
        $pages = $this->getIncunable()->getScans()->count();
        $current = 0;
        foreach($this->getIncunable()->getScans() as $scan)
        {
            $current++;
            if($this->getId() == $scan->getId()){
                return $current . " of " . $pages;
            }
        }

        return "n/a of " . $pages;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getModified(): ?\DateTimeInterface
    {
        return $this->modified;
    }

    public function setModified(\DateTimeInterface $modified): self
    {
        $this->modified = $modified;

        return $this;
    }

    public function getIncunable(): ?Incunable
    {
        return $this->incunable;
    }

    public function setIncunable(?Incunable $incunable): self
    {
        $this->incunable = $incunable;

        return $this;
    }

    public function getPublicPath(){
        return "/images/scans/" . md5($this->getPath()) . '.jpg';
    }

    public function getHeaderPath(){
        return "/images/headers/" . md5($this->getPath()) . '.jpg';
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }
}
